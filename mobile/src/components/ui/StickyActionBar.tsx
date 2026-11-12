import type { ReactNode } from 'react';
import { StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { colors } from '@/theme/tokens';

/** White bar pinned under the scrolling content. */
export function StickyActionBar({ children, aboveTabs = false }: { children: ReactNode; aboveTabs?: boolean }) {
    const insets = useSafeAreaInsets();

    return <View style={[styles.bar, { paddingBottom: 12 + (aboveTabs ? 0 : insets.bottom) }]}>{children}</View>;
}

const styles = StyleSheet.create({
    bar: { backgroundColor: colors.white, borderTopWidth: 1, borderTopColor: colors.line, paddingTop: 10, paddingHorizontal: 14 },
});
