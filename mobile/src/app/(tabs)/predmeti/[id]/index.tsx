import { useLocalSearchParams } from 'expo-router';
import { RefreshControl, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import type { AgendaRow, MatterHeader as Header } from '@shared/types/agenda';
import { MatterHeader } from '@/components/matters/MatterHeader';
import { TimelineRow } from '@/components/matters/TimelineRow';
import { ScreenState } from '@/components/ui/ScreenState';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { Text } from '@/components/ui/Text';
import { useScreen } from '@/lib/useScreen';
import { colors } from '@/theme/tokens';

type Timeline = { matter: Header; upcoming: AgendaRow[]; recorded: AgendaRow[] };

export default function TimelineScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, loading, reload } = useScreen<Timeline>(`/matters/${id}`);

    return (
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.white }}>
            {!data ? (
                <ScreenState error={error} onRetry={reload} />
            ) : (
                <ScrollView
                    style={{ backgroundColor: colors.app }}
                    stickyHeaderIndices={[]}
                    refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}
                >
                    <MatterHeader matter={data.matter} active="timeline" />
                    <View style={{ paddingHorizontal: 18, paddingTop: 6, paddingBottom: 20 }}>
                        <SectionLabel style={{ paddingTop: 14, paddingBottom: 8 }}>{bs.matters.upcoming}</SectionLabel>
                        {data.upcoming.map((row) => (
                            <TimelineRow key={row.id} row={row} />
                        ))}
                        {data.upcoming.length === 0 ? (
                            <Text size={14} color={colors.muted} style={{ paddingBottom: 8 }}>
                                {bs.matters.empty}
                            </Text>
                        ) : null}
                        <SectionLabel style={{ paddingTop: 14, paddingBottom: 8 }}>{bs.matters.recorded}</SectionLabel>
                        {data.recorded.map((row) => (
                            <TimelineRow key={row.id} row={row} />
                        ))}
                    </View>
                </ScrollView>
            )}
        </SafeAreaView>
    );
}
