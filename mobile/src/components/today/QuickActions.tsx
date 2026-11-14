import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { StickyActionBar } from '@/components/ui/StickyActionBar';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

/** "Brzi unos": Fotografiši (primary, 1.35fr), Diktiraj (only with a speech service), Podnesak. */
export function QuickActions({ canDictate = false }: { canDictate?: boolean }) {
    return (
        <StickyActionBar aboveTabs>
            <SectionLabel style={{ marginBottom: 7 }}>{bs.today.quickInput}</SectionLabel>
            <View style={styles.row}>
                <Pressable accessibilityRole="button" onPress={() => router.push('/posta/fotografisi')} style={[styles.button, styles.primary, { flex: 1.35 }]}>
                    <View style={styles.camera}>
                        <View style={styles.lens} />
                    </View>
                    <Text size={15} weight={600} color={colors.white}>
                        {bs.today.capture}
                    </Text>
                </Pressable>
                {canDictate ? (
                    <Pressable accessibilityRole="button" onPress={() => router.push('/diktat')} style={[styles.button, styles.outline]}>
                        <Text size={15} weight={600}>
                            {bs.today.dictate}
                        </Text>
                    </Pressable>
                ) : null}
                <Pressable accessibilityRole="button" onPress={() => router.push('/posta/podnesak')} style={[styles.button, styles.outline]}>
                    <Text size={15} weight={600}>
                        {bs.today.submission}
                    </Text>
                </Pressable>
            </View>
        </StickyActionBar>
    );
}

const styles = StyleSheet.create({
    row: { flexDirection: 'row', gap: 8 },
    button: { flex: 1, height: 56, borderRadius: radius.card, alignItems: 'center', justifyContent: 'center', flexDirection: 'row', gap: 9 },
    primary: { backgroundColor: colors.ink },
    outline: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.ink },
    camera: { width: 20, height: 14, borderWidth: 2, borderColor: colors.white, borderRadius: radius.chip, alignItems: 'center', justifyContent: 'center' },
    lens: { width: 6, height: 6, borderRadius: 3, backgroundColor: colors.white },
});
