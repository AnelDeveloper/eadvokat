import { createContext, type ReactNode, useCallback, useContext, useMemo, useRef, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Text } from '@/components/ui/Text';
import { colors, radius, shadow, size } from '@/theme/tokens';

const TOAST_MS = 3400;

const ToastContext = createContext<(message: string) => void>(() => {});

export function ToastProvider({ children }: { children: ReactNode }) {
    const [message, setMessage] = useState('');
    const timer = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);
    const insets = useSafeAreaInsets();

    const show = useCallback((text: string) => {
        clearTimeout(timer.current);
        setMessage(text);
        timer.current = setTimeout(() => setMessage(''), TOAST_MS);
    }, []);

    const value = useMemo(() => show, [show]);

    return (
        <ToastContext.Provider value={value}>
            {children}
            {message !== '' && (
                <View
                    pointerEvents="none"
                    accessibilityLiveRegion="polite"
                    style={[styles.toast, { bottom: size.tabBar + 18 + insets.bottom }]}
                >
                    <Text size={14} lineHeight={1.35} color={colors.white}>
                        {message}
                    </Text>
                </View>
            )}
        </ToastContext.Provider>
    );
}

export function useToast(): (message: string) => void {
    return useContext(ToastContext);
}

const styles = StyleSheet.create({
    toast: {
        position: 'absolute',
        left: 14,
        right: 14,
        paddingVertical: 12,
        paddingHorizontal: 14,
        borderRadius: radius.card,
        backgroundColor: colors.ink,
        zIndex: 50,
        ...shadow.toast,
    },
});
