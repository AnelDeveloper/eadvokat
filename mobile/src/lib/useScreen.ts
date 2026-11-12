import { useFocusEffect } from 'expo-router';
import { useCallback, useRef, useState } from 'react';
import { api, ApiError } from '@/lib/api';

/**
 * Loads a screen's data from the API and reloads it whenever the screen comes
 * back into focus, so confirmations elsewhere show up immediately.
 */
export function useScreen<T>(path: string | null) {
    const [data, setData] = useState<T | null>(null);
    const [error, setError] = useState<ApiError | null>(null);
    const [loading, setLoading] = useState(true);
    // Only the latest request may update the screen.
    const requestId = useRef(0);

    const reload = useCallback(async () => {
        if (!path) {
            return;
        }

        const id = ++requestId.current;
        setLoading(true);

        try {
            const result = await api.get<T>(path);

            if (id === requestId.current) {
                setData(result);
                setError(null);
            }
        } catch (e) {
            setError(e instanceof ApiError ? e : new ApiError(String(e), 0));
        } finally {
            setLoading(false);
        }
    }, [path]);

    useFocusEffect(
        useCallback(() => {
            void reload();
        }, [reload]),
    );

    return { data, error, loading, reload, setData };
}
