import { useLocalSearchParams } from 'expo-router';
import { RefreshControl, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import type { MatterHeader as Header } from '@shared/types/agenda';
import { MatterHeader } from '@/components/matters/MatterHeader';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { useScreen } from '@/lib/useScreen';
import { colors } from '@/theme/tokens';

type Audit = { matter: Header; entries: { id: number; at: string; user: string; description: string }[] };

export default function AuditScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, loading, reload } = useScreen<Audit>(`/matters/${id}/audit`);

    return (
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.white }}>
            {!data ? (
                <ScreenState error={error} onRetry={reload} />
            ) : (
                <ScrollView style={{ backgroundColor: colors.app }} refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}>
                    <MatterHeader matter={data.matter} active="audit" />
                    <View style={{ paddingHorizontal: 18, paddingTop: 14, paddingBottom: 22 }}>
                        <Text size={13} color={colors.muted} style={{ marginBottom: 6 }}>
                            {bs.matters.auditIntro}
                        </Text>
                        {data.entries.map((entry) => (
                            <View key={entry.id} style={{ borderTopWidth: 1, borderTopColor: colors.line, paddingVertical: 10 }}>
                                <Text family="mono" size={12} color={colors.muted}>
                                    {entry.at} · {entry.user}
                                </Text>
                                <Text size={14} lineHeight={1.4} style={{ marginTop: 2 }}>
                                    {entry.description}
                                </Text>
                            </View>
                        ))}
                    </View>
                </ScrollView>
            )}
        </SafeAreaView>
    );
}
