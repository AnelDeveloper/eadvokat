import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Text } from '@/components/ui/Text';
import { colors } from '@/theme/tokens';

/** White top bar of full-screen flows: back · title · step. */
export function ScreenBar({ title, step, onBack, dark = false }: { title: string; step?: string; onBack?: () => void; dark?: boolean }) {
    const insets = useSafeAreaInsets();
    const back = onBack ?? (() => (router.canGoBack() ? router.back() : router.replace('/danas')));

    return (
        <View style={[styles.bar, { paddingTop: insets.top, backgroundColor: dark ? colors.inkDeep : colors.white, borderBottomWidth: dark ? 0 : 1 }]}>
            <Pressable onPress={back} accessibilityRole="button" accessibilityLabel={bs.common.back} style={styles.back}>
                <Text size={24} color={dark ? colors.white : colors.ink}>
                    ‹
                </Text>
            </Pressable>
            <Text size={16} weight={600} align="center" color={dark ? colors.white : colors.ink} style={{ flex: 1 }} accessibilityRole="header">
                {title}
            </Text>
            <Text family="mono" size={12} color={colors.muted} align="center" style={{ width: 44 }}>
                {step ?? ''}
            </Text>
        </View>
    );
}

const styles = StyleSheet.create({
    bar: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, borderBottomColor: colors.line },
    back: { width: 44, height: 48, alignItems: 'center', justifyContent: 'center' },
});
