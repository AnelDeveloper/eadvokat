import { Pressable, RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { go } from '@/lib/navigate';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

type Matters = {
    matters: { id: number; caseNumber: string; title: string; meta: string; next: string; costLine: string }[];
    isDemo: boolean;
};

export default function MattersScreen() {
    const { data, error, loading, reload } = useScreen<Matters>('/matters');

    return (
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.app }}>
            {!data ? (
                <ScreenState error={error} onRetry={reload} />
            ) : (
                <ScrollView contentContainerStyle={styles.content} refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}>
                    <View style={styles.head}>
                        <Text size={30} weight={600} lineHeight={1.1} accessibilityRole="header">
                            {bs.matters.title}
                        </Text>
                        <Button label={bs.matters.create} size="sm" onPress={() => go('/predmeti/novi')} />
                    </View>
                    {data.matters.length === 0 ? (
                        <View style={styles.empty}>
                            <Text size={15} color={colors.muted}>
                                {bs.matters.emptyList}
                            </Text>
                        </View>
                    ) : null}
                    <View style={{ gap: 10 }}>
                        {data.matters.map((matter) => (
                            <Pressable key={matter.id} accessibilityRole="button" onPress={() => go(`/predmeti/${matter.id}`)} style={styles.card}>
                                <View style={styles.case}>
                                    <Text family="mono" size={15} weight={500}>
                                        {matter.caseNumber}
                                    </Text>
                                </View>
                                <View style={{ padding: 14, paddingTop: 12 }}>
                                    <Text size={17} weight={600} lineHeight={1.3}>
                                        {matter.title}
                                    </Text>
                                    <Text size={13} color={colors.muted} style={{ marginTop: 3 }}>
                                        {matter.meta}
                                    </Text>
                                    <View style={styles.next}>
                                        <Text size={13.5}>
                                            {bs.matters.next} <Text size={13.5} weight={600}>{matter.next}</Text>
                                        </Text>
                                        <Text size={13.5} style={{ marginTop: 3 }}>
                                            {bs.matters.costs} <Text size={13.5} weight={600}>{matter.costLine}</Text>
                                        </Text>
                                    </View>
                                </View>
                            </Pressable>
                        ))}
                    </View>
                    {data.isDemo ? (
                        <Text size={13} color={colors.muted} style={{ marginTop: 14, marginHorizontal: 2 }}>
                            {bs.matters.demoNote}
                        </Text>
                    ) : null}
                </ScrollView>
            )}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    head: { marginTop: 8, marginBottom: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
    empty: { borderWidth: 1, borderStyle: 'dashed', borderColor: colors.lineStrong, borderRadius: radius.card, backgroundColor: colors.white, padding: 16 },
    content: { paddingHorizontal: 18, paddingTop: 6, paddingBottom: 18 },
    card: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, overflow: 'hidden' },
    case: { paddingHorizontal: 14, paddingVertical: 12, borderBottomWidth: 2, borderBottomColor: colors.ink },
    next: { marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: colors.hairline },
});
