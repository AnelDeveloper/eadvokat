import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const KEY = 'rokovnik.token';

/** The API token lives in the device keychain / keystore (localStorage only on web dev builds). */
export const tokenStore = {
    async get(): Promise<string | null> {
        if (Platform.OS === 'web') {
            return globalThis.localStorage?.getItem(KEY) ?? null;
        }

        return SecureStore.getItemAsync(KEY);
    },
    async set(token: string): Promise<void> {
        if (Platform.OS === 'web') {
            globalThis.localStorage?.setItem(KEY, token);

            return;
        }

        await SecureStore.setItemAsync(KEY, token);
    },
    async clear(): Promise<void> {
        if (Platform.OS === 'web') {
            globalThis.localStorage?.removeItem(KEY);

            return;
        }

        await SecureStore.deleteItemAsync(KEY);
    },
};
