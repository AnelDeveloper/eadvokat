import { type Href, router } from 'expo-router';

/**
 * The API returns web paths (/predmeti/1, /rokovi/5/prijedlog, …). The app's
 * routes mirror them, so they can be opened as they are.
 */
export function go(path: string | null | undefined, replace = false): void {
    if (!path) {
        return;
    }

    if (replace) {
        router.replace(path as Href);
    } else {
        router.push(path as Href);
    }
}
