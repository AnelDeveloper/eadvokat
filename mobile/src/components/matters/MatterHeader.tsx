import { router } from 'expo-router';
import { Pressable, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import type { MatterHeader as Header } from '@shared/types/agenda';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

export type MatterTab = 'timeline' | 'costs' | 'audit';

const tabPath: Record<MatterTab, (id: number) => string> = {
    timeline: (id) => `/predmeti/${id}`,
    costs: (id) => `/predmeti/${id}/troskovnik`,
    audit: (id) => `/predmeti/${id}/izmjene`,
};

export function MatterHeader({ matter, active }: { matter: Header; active: MatterTab }) {
    return (
        <View>
            <View style={styles.header}>
                <Pressable onPress={() => router.navigate('/predmeti')} accessibilityRole="link" style={styles.back}>
                    <Text size={15} weight={500}>
                        {bs.matters.back}
                    </Text>
                </Pressable>
                <Text family="mono" size={17} weight={500}>
                    {matter.caseNumber}
                </Text>
                <Text size={19} weight={600} lineHeight={1.25} style={{ marginTop: 4 }} accessibilityRole="header">
                    {matter.title}
                </Text>
                <Text size={13} color={colors.muted} style={{ marginTop: 3 }}>
                    {matter.meta}
                </Text>
                <Pressable accessibilityRole="button" onPress={() => router.replace(tabPath.costs(matter.id) as never)} style={styles.costs}>
                    <View>
                        <Text size={16} weight={600} color={colors.white}>
                            {bs.matters.costButton}
                        </Text>
                        <Text size={12.5} color={colors.line}>
                            {matter.costLine}
                        </Text>
                    </View>
                    <Text size={20} color={colors.white}>
                        ›
                    </Text>
                </Pressable>
            </View>
            <View style={styles.tabs} accessibilityRole="tablist">
                {(['timeline', 'costs', 'audit'] as const).map((tab) => {
                    const on = tab === active;

                    return (
                        <Pressable
                            key={tab}
                            accessibilityRole="tab"
                            accessibilityState={{ selected: on }}
                            onPress={() => !on && router.replace(tabPath[tab](matter.id) as never)}
                            style={[styles.tab, { borderBottomColor: on ? colors.ink : colors.transparent }]}
                        >
                            <Text size={14.5} weight={on ? 600 : 500} color={on ? colors.ink : colors.muted}>
                                {bs.matters.tabs[tab]}
                            </Text>
                        </Pressable>
                    );
                })}
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    header: { backgroundColor: colors.white, paddingTop: 2, paddingBottom: 14, paddingHorizontal: 18, borderBottomWidth: 1, borderBottomColor: colors.line },
    back: { height: 44, justifyContent: 'center', alignSelf: 'flex-start' },
    costs: { marginTop: 12, minHeight: 56, borderRadius: radius.card, backgroundColor: colors.ink, paddingHorizontal: 14, paddingVertical: 8, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    tabs: { flexDirection: 'row', backgroundColor: colors.white, borderBottomWidth: 1, borderBottomColor: colors.line },
    tab: { flex: 1, height: 46, alignItems: 'center', justifyContent: 'center', borderBottomWidth: 3 },
});
