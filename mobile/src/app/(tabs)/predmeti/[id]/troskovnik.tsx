import { useLocalSearchParams } from 'expo-router';
import { useState } from 'react';
import { Pressable, RefreshControl, ScrollView, StyleSheet, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import type { MatterHeader as Header } from '@shared/types/agenda';
import { MatterHeader } from '@/components/matters/MatterHeader';
import { Button } from '@/components/ui/Button';
import { MoneyField } from '@/components/ui/MoneyField';
import { ScreenState } from '@/components/ui/ScreenState';
import { SegmentedControl } from '@/components/ui/SegmentedControl';
import { StatusChip } from '@/components/ui/StatusChip';
import { Text } from '@/components/ui/Text';
import { api, ApiError } from '@/lib/api';
import { shareCostSheetPdf } from '@/lib/pdf';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, fonts, radius } from '@/theme/tokens';

type Item = { id: number; title: string; date: string; source: string; documentId: number | null; amount: string | null; confirmed: boolean };
type Costs = {
    matter: Header;
    sheet: { id: number; approved: boolean; jurisdiction: string; tariffSource: string; pending: number; pendingLabel: string | null; total: string; items: Item[] };
    isDemo: boolean;
    approvedLine: string | null;
    jurisdictions: { value: string; label: string }[];
};

const t = bs.costs;

export default function CostsScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, loading, reload } = useScreen<Costs>(`/matters/${id}/costs`);
    const toast = useToast();
    // Local edits of the tariff source; reset whenever the server value changes.
    const [tariffDraft, setTariffDraft] = useState<{ server: string; value: string } | null>(null);
    const serverTariff = data?.sheet.tariffSource ?? '';
    const tariff = tariffDraft && tariffDraft.server === serverTariff ? tariffDraft.value : serverTariff;
    const setTariff = (value: string) => setTariffDraft({ server: serverTariff, value });

    async function run(action: () => Promise<{ message: string | null }>): Promise<void> {
        try {
            const result = await action();

            if (result.message) {
                toast(result.message);
            }
        } catch (e) {
            toast(e instanceof ApiError ? e.first() : bs.mobile.genericError);
        }

        await reload();
    }

    if (!data) {
        return (
            <SafeAreaView style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenState error={error} onRetry={reload} />
            </SafeAreaView>
        );
    }

    const { sheet } = data;
    const allConfirmed = sheet.pending === 0 && sheet.items.length > 0;

    function approve(): void {
        if (sheet.approved) {
            return;
        }

        if (!allConfirmed) {
            toast(t.confirmAllToast);

            return;
        }

        if (tariff.trim() === '') {
            toast(t.tariffToast);

            return;
        }

        void run(() => api.post(`/matters/${id}/costs/approve`));
    }

    return (
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.white }}>
            <ScrollView style={{ backgroundColor: colors.app }} keyboardShouldPersistTaps="handled" refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}>
                <MatterHeader matter={data.matter} active="costs" />
                <View style={{ paddingHorizontal: 18, paddingTop: 14, paddingBottom: 22 }}>
                    <View style={styles.between}>
                        <Text size={19} weight={600}>
                            {t.title}
                        </Text>
                        <StatusChip status={sheet.approved ? 'confirmed' : 'proposed'} label={sheet.approved ? t.chipApproved : t.chipDraft} />
                    </View>
                    <View style={styles.notice}>
                        <Text size={13} lineHeight={1.4} color={colors.proposedText}>
                            {data.isDemo ? (
                                <>
                                    {t.demoNotice}{' '}
                                    <Text size={13} weight={600} color={colors.proposedText}>
                                        {t.demoNoticeBold}
                                    </Text>
                                    {t.demoNoticeEnd}
                                </>
                            ) : (
                                t.notice
                            )}
                        </Text>
                    </View>

                    <Text size={12} weight={500} color={colors.muted} style={{ marginTop: 14 }}>
                        {t.jurisdiction}
                    </Text>
                    <SegmentedControl
                        value={sheet.jurisdiction}
                        options={data.jurisdictions}
                        onChange={(value) => void run(() => api.patch(`/matters/${id}/costs`, { jurisdiction: value, tariff_source: tariff }))}
                        style={{ marginTop: 5 }}
                    />

                    <Text size={12} weight={500} color={colors.muted} style={{ marginTop: 12 }}>
                        {t.tariff}
                    </Text>
                    <TextInput
                        accessibilityLabel={t.tariff}
                        value={tariff}
                        onChangeText={setTariff}
                        onEndEditing={() => tariff !== sheet.tariffSource && void run(() => api.patch(`/matters/${id}/costs`, { jurisdiction: sheet.jurisdiction, tariff_source: tariff }))}
                        placeholder={t.tariffPlaceholder}
                        placeholderTextColor={colors.faint}
                        style={styles.input}
                    />
                    <Text size={12} color={colors.muted} style={{ marginTop: 5 }}>
                        {data.isDemo ? t.tariffNoteDemo : t.tariffNote}
                    </Text>

                    <View style={styles.list}>
                        {sheet.items.map((item) => (
                            <View key={item.id} style={styles.item}>
                                <View style={styles.between}>
                                    <View style={{ flex: 1, minWidth: 0 }}>
                                        <Text size={15} weight={500} lineHeight={1.3}>
                                            {item.title}
                                        </Text>
                                        <Text family="mono" size={12} color={colors.muted} style={{ marginTop: 2 }}>
                                            {item.date} · {item.source}
                                        </Text>
                                    </View>
                                    <StatusChip status={item.confirmed ? 'confirmed' : 'proposed'} />
                                </View>
                                <View style={styles.controls}>
                                    <MoneyField
                                        value={item.amount}
                                        suffix={data.isDemo ? t.suffixDemo : t.suffix}
                                        label={item.title}
                                        onChange={(amount) => amount !== item.amount && void run(() => api.patch(`/cost-items/${item.id}`, { amount }))}
                                    />
                                    <Pressable
                                        accessibilityRole="button"
                                        onPress={() =>
                                            !item.confirmed && item.amount === null
                                                ? toast(t.amountToast)
                                                : void run(() => api.post(`/cost-items/${item.id}/${item.confirmed ? 'revert' : 'confirm'}`))
                                        }
                                        style={[styles.toggle, { backgroundColor: item.confirmed ? colors.white : colors.ink }]}
                                    >
                                        <Text size={13.5} weight={600} color={item.confirmed ? colors.ink : colors.white}>
                                            {item.confirmed ? t.revert : t.confirm}
                                        </Text>
                                    </Pressable>
                                    <Pressable accessibilityRole="button" accessibilityLabel={t.remove} onPress={() => void run(() => api.delete(`/cost-items/${item.id}`))} style={styles.remove}>
                                        <Text size={18} color={colors.urgent}>
                                            ×
                                        </Text>
                                    </Pressable>
                                </View>
                            </View>
                        ))}
                        {sheet.items.length === 0 ? (
                            <Text size={14} color={colors.muted} style={{ paddingVertical: 12 }}>
                                {t.empty}
                            </Text>
                        ) : null}
                        <View style={[styles.between, { paddingVertical: 12, alignItems: 'baseline' }]}>
                            <Text size={14} weight={600}>
                                {data.isDemo ? t.totalDemo : t.total}
                            </Text>
                            <Text family="mono" size={18} weight={500}>
                                {sheet.total} KM
                            </Text>
                        </View>
                    </View>

                    <Button
                        label={sheet.approved ? t.approved : allConfirmed ? t.approve : t.confirmFirst(sheet.pendingLabel ?? '')}
                        variant={sheet.approved ? 'confirm' : allConfirmed ? 'primary' : 'inactive'}
                        onPress={approve}
                    />
                    {data.approvedLine ? (
                        <Text size={12} color={colors.confirmedText} style={{ marginTop: 6 }}>
                            {data.approvedLine}
                        </Text>
                    ) : null}
                    <Button
                        label={t.export}
                        variant="subtle"
                        size="md"
                        weight={500}
                        style={{ marginTop: 8 }}
                        onPress={() =>
                            void shareCostSheetPdf(data.matter.id, `troskovnik-nacrt-${data.matter.caseNumber.replace(/\s+/g, '-').toLowerCase()}.pdf`).catch(() =>
                                toast(bs.mobile.exportFailed),
                            )
                        }
                    />
                    <Text size={12} color={colors.muted} style={{ marginTop: 8 }}>
                        {t.nothingSent}
                    </Text>
                </View>
            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    between: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', gap: 10 },
    notice: { marginTop: 10, paddingVertical: 10, paddingHorizontal: 12, borderWidth: 1, borderStyle: 'dashed', borderColor: colors.proposed, borderRadius: radius.field, backgroundColor: colors.proposedBg },
    input: { marginTop: 5, minHeight: 44, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: radius.field, backgroundColor: colors.white, paddingHorizontal: 12, fontFamily: fonts.sans400, fontSize: 14, color: colors.ink },
    list: { marginTop: 16, borderTopWidth: 2, borderTopColor: colors.ink },
    item: { paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: colors.line },
    controls: { flexDirection: 'row', gap: 6, marginTop: 9, alignItems: 'center' },
    toggle: { minHeight: 44, paddingHorizontal: 12, borderWidth: 1, borderColor: colors.ink, borderRadius: radius.field, justifyContent: 'center' },
    remove: { width: 44, height: 44, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: radius.field, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.white },
});
