import { createContext, type ReactNode, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { Platform } from 'react-native';
import { api, onApiUnauthorized, setApiToken } from '@/lib/api';
import { tokenStore } from '@/lib/tokenStore';

export type RegisterInput = {
    office_name: string;
    title: string;
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export type User = { id: number; name: string; displayName: string; email: string; isDemo: boolean };

type AuthState = {
    ready: boolean;
    user: User | null;
    signIn: (email: string, password: string) => Promise<void>;
    register: (input: RegisterInput) => Promise<void>;
    signOut: () => Promise<void>;
};

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [ready, setReady] = useState(false);
    const [user, setUser] = useState<User | null>(null);

    const forget = useCallback(async () => {
        setApiToken(null);
        setUser(null);
        await tokenStore.clear();
    }, []);

    useEffect(() => {
        onApiUnauthorized(() => void forget());

        void (async () => {
            const token = await tokenStore.get();

            if (token) {
                setApiToken(token);

                try {
                    setUser((await api.get<{ user: User }>('/me')).user);
                } catch {
                    await forget();
                }
            }

            setReady(true);
        })();
    }, [forget]);

    const signIn = useCallback(async (email: string, password: string) => {
        const result = await api.post<{ token: string; user: User }>('/auth/token', {
            email: email.trim().toLowerCase(),
            password,
            device_name: `Ročište ${Platform.OS}`,
        });

        setApiToken(result.token);
        await tokenStore.set(result.token);
        setUser(result.user);
    }, []);

    const register = useCallback(async (input: RegisterInput) => {
        const result = await api.post<{ token: string; user: User }>('/auth/register', {
            ...input,
            email: input.email.trim().toLowerCase(),
            device_name: `Ročište ${Platform.OS}`,
        });

        setApiToken(result.token);
        await tokenStore.set(result.token);
        setUser(result.user);
    }, []);

    const signOut = useCallback(async () => {
        try {
            await api.delete('/auth/token');
        } finally {
            await forget();
        }
    }, [forget]);

    const value = useMemo(() => ({ ready, user, signIn, register, signOut }), [ready, user, signIn, register, signOut]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
    const value = useContext(AuthContext);

    if (!value) {
        throw new Error('useAuth must be used inside AuthProvider');
    }

    return value;
}
