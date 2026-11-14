import { router } from 'expo-router';
import { RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import type { BriefingGroup, BriefingRow } from '@shared/types/today';
import { BriefingCard } from '@/components/today/BriefingCard';
import { QuickActions } from '@/components/today/QuickActions';
import { Button } from '@/components/ui/Button';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { api } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useScreen } from '@/lib/useScreen';
import { colors } from '@/theme/tokens';

type Today = { date: string; lawyer: string; groups: BriefingGroup[]; canDictate?: boolean };

export default function TodayScreen() {
    const { data, error, loading, reload } = useScreen<Today>('/today');

    async function onRow(row: BriefingRow): Promise<void> {
        if (row.toggleUrl) {
            const id = row.id.split(':')[1];
            await api.post(`/reminders/${id}/toggle`);
            await reload();

            return;
        }

        go(row.href);
    }

    return (
        <SafeAreaView edges={['top']} style={styles.page}>
            {!data ? (
                <ScreenState error={error} onRetry={reload} />
            ) : (
                <>
                    <ScrollView
                        contentContainerStyle={styles.content}
                        refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}
                    >
                        <View style={styles.header}>
                            <View>
                                <Text family="mono" size={12} color={colors.muted}>
                                    {data.date}
                                </Text>
                                <Text size={30} weight={600} lineHeight={1.1} accessibilityRole="header">
                                    {bs.today.title}
                                </Text>
                            </View>
                            <Button label={bs.today.settings} variant="subtle" size="sm" weight={500} onPress={() => router.push('/postavke')} />
                        </View>
                        <BriefingCard lawyer={data.lawyer} groups={data.groups} onRow={onRow} />
                    </ScrollView>
                    <QuickActions canDictate={data?.canDictate ?? false} />
                </>
            )}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    page: { flex: 1, backgroundColor: colors.app },
    content: { paddingHorizontal: 18, paddingTop: 6, paddingBottom: 18 },
    header: { flexDirection: 'row', alignItems: 'flex-end', justifyContent: 'space-between', gap: 12 },
});
