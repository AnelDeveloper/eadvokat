import { bs } from '@shared/i18n/bs';

export const API_URL = (process.env.EXPO_PUBLIC_API_URL ?? 'http://127.0.0.1:8037').replace(/\/$/, '');

let currentToken: string | null = null;
let onUnauthorized: (() => void) | null = null;

export function setApiToken(token: string | null): void {
    currentToken = token;
}

export function onApiUnauthorized(handler: () => void): void {
    onUnauthorized = handler;
}

export function authHeaders(): Record<string, string> {
    return currentToken ? { Authorization: `Bearer ${currentToken}` } : {};
}

/** Response of every mutation: an optional toast and the next app path. */
export type ActionResult = { message: string | null; next: string | null };

export class ApiError extends Error {
    constructor(
        message: string,
        public readonly status: number,
        public readonly errors: Record<string, string[]> = {},
        public readonly body: unknown = null,
    ) {
        super(message);
    }

    /** The first validation message, as the web app shows it in a toast. */
    first(): string {
        const first = Object.values(this.errors)[0]?.[0];

        return first ?? this.message;
    }
}

type Upload = { uri: string; name: string; type: string };
type Body = Record<string, unknown> | FormData | undefined;

async function request<T>(method: string, path: string, body?: Body): Promise<T> {
    const isForm = typeof FormData !== 'undefined' && body instanceof FormData;
    let response: Response;

    try {
        response = await fetch(`${API_URL}/api/v1${path}`, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body && !isForm ? { 'Content-Type': 'application/json' } : {}),
                ...authHeaders(),
            },
            body: body === undefined ? undefined : isForm ? body : JSON.stringify(body),
        });
    } catch {
        throw new ApiError(bs.mobile.offline, 0);
    }

    const data: unknown = response.status === 204 ? null : await response.json().catch(() => null);

    if (response.status === 401) {
        onUnauthorized?.();
    }

    if (!response.ok) {
        const payload = (data ?? {}) as { message?: string; errors?: Record<string, string[]> };

        throw new ApiError(payload.message ?? bs.mobile.genericError, response.status, payload.errors ?? {}, data);
    }

    return data as T;
}

export const api = {
    get: <T>(path: string) => request<T>('GET', path),
    post: <T = ActionResult>(path: string, body?: Body) => request<T>('POST', path, body ?? {}),
    patch: <T = ActionResult>(path: string, body?: Body) => request<T>('PATCH', path, body ?? {}),
    delete: <T = ActionResult>(path: string) => request<T>('DELETE', path),
};

/** Multipart form with files, for captured documents, recordings and proofs. */
export function formData(fields: Record<string, string | number | boolean | number[] | Upload | null | undefined>): FormData {
    const form = new FormData();

    for (const [key, value] of Object.entries(fields)) {
        if (value === null || value === undefined) {
            continue;
        }

        if (Array.isArray(value)) {
            value.forEach((v) => form.append(`${key}[]`, String(v)));
        } else if (typeof value === 'object') {
            // React Native's FormData accepts { uri, name, type } file descriptors.
            form.append(key, value as unknown as Blob);
        } else {
            form.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value));
        }
    }

    return form;
}
