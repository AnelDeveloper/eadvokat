import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { addDays, daysBetween, formatDate, formatDayMonth, weekday } from '@shared/lib/format';
import { Button } from '@/components/ui/Button';
import { Checkbox } from '@/components/ui/Checkbox';
import { DateField } from '@/components/ui/DateField';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { ScreenState } from '@/components/ui/ScreenState';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { SegmentedControl } from '@/components/ui/SegmentedControl';
import { SourceQuote } from '@/components/ui/SourceQuote';
import { StickyActionBar } from '@/components/ui/StickyActionBar';
import { Text } from '@/components/ui/Text';
import { api, ApiError } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

type Proposal = {
    deadline: {
        id: number;
        matterId: number;
        title: string;
        ruleText: string;
        days: number | null;
        quote: string | null;
        highlight: string | null;
        sourceMeta: string;
        sourceDocumentId: number | null;
        jurisdiction: string;
        startDate: string;
        dueDate: string;
        remind3: boolean;
        remind1: boolean;
        isConfirmed: boolean;
    };
    jurisdictions: { value: string; label: string }[];
};

const t = bs.deadline;

export default function DeadlineProposalScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, reload } = useScreen<Proposal>(`/deadlines/${id}`);

    useEffect(() => {
        if (error?.status === 409) {
            go((error.body as { next?: string } | null)?.next ?? '/danas', true);
        }
    }, [error]);

    if (!data) {
        return (
            <View style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenBar title={t.title} step={t.step} />
                <ScreenState error={error?.status === 409 ? null : error} onRetry={reload} />
            </View>
        );
    }

    return <ProposalForm data={data} />;
}

function ProposalForm({ data }: { data: Proposal }) {
    const toast = useToast();
    const d = data.deadline;
    const [jurisdiction, setJurisdiction] = useState(d.jurisdiction);
    const [start, setStart] = useState<string | null>(d.startDate);
    const [due, setDue] = useState<string | null>(d.dueDate);
    // Acknowledgements always start unticked: each confirmation is a fresh, explicit act.
    const [acks, setAcks] = useState({ rule: false, start: false, due: false });
    const [remind3, setRemind3] = useState(d.remind3);
    const [remind1, setRemind1] = useState(d.remind1);
    const [busy, setBusy] = useState(false);

    const checked = [acks.rule, acks.start, acks.due].filter(Boolean).length;
    const elapsed = start && due ? daysBetween(start, due) : null;
    const manual = d.days !== null && elapsed !== d.days;
    const dueInfo = !due || elapsed === null ? bs.common.dateHint : manual || d.days === null ? t.dueManual(weekday(due), elapsed, d.days ?? elapsed) : t.dueComputed(weekday(due), elapsed);
    const reminderBase = due ?? d.dueDate;

    // Rule 3: changing the start recomputes the last day and clears checks 2 and 3.
    function changeStart(value: string | null): void {
        setStart(value);

        if (value && d.days !== null) {
            setDue(addDays(value, d.days));
        }

        setAcks((a) => ({ ...a, start: false, due: false }));
    }

    function changeDue(value: string | null): void {
        setDue(value);
        setAcks((a) => ({ ...a, due: false }));
    }

    function changeJurisdiction(value: string): void {
        setJurisdiction(value);
        setAcks((a) => ({ ...a, rule: false }));
    }

    function later(): void {
        if (!d.isConfirmed) {
            toast(t.laterToast);
        }

        if (router.canGoBack()) {
            router.back();
        } else {
            router.replace('/danas');
        }
    }

    async function confirm(): Promise<void> {
        if (checked < 3) {
            toast(t.needsChecksToast);

            return;
        }

        if (!due) {
            toast(t.invalidDue);

            return;
        }

        setBusy(true);

        try {
            const result = await api.post(`/deadlines/${d.id}/confirm`, {
                jurisdiction,
                start_date: start,
                due_date: due,
                rule_ack: acks.rule,
                start_ack: acks.start,
                due_ack: acks.due,
                remind_3_days: remind3,
                remind_1_day: remind1,
            });

            if (result.message) {
                toast(result.message);
            }

            go(result.next, true);
        } catch (e) {
            toast(e instanceof ApiError ? e.first() : bs.mobile.genericError);
        } finally {
            setBusy(false);
        }
    }

    return (
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1, backgroundColor: colors.app }}>
            <ScreenBar title={t.title} step={t.step} onBack={later} />
            <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingTop: 14, paddingBottom: 18 }} keyboardShouldPersistTaps="handled">
                <View style={[styles.banner, d.isConfirmed ? styles.bannerOk : styles.bannerProposed]}>
                    <Text size={13.5} lineHeight={1.4} color={d.isConfirmed ? colors.confirmedText : colors.proposedText}>
                        <Text size={13.5} weight={600} color={d.isConfirmed ? colors.confirmedText : colors.proposedText}>
                            {d.isConfirmed ? t.confirmedBanner : t.banner}
                        </Text>{' '}
                        {d.isConfirmed ? t.confirmedBannerText : t.bannerText}
                    </Text>
                </View>

                {d.quote ? (
                    <>
                        <SectionLabel style={{ marginTop: 14 }}>{t.source}</SectionLabel>
                        <SourceQuote
                            text={d.quote}
                            highlight={d.highlight}
                            meta={d.sourceMeta}
                            onOpen={d.sourceDocumentId ? () => go(`/dokumenti/${d.sourceDocumentId}`) : undefined}
                            style={{ marginTop: 6 }}
                        />
                    </>
                ) : null}

                <View style={[styles.card, { marginTop: 12 }]}>
                    <Text size={12} weight={500} color={colors.muted}>
                        {t.rule}
                    </Text>
                    <Text size={15} weight={500} style={{ marginTop: 2 }}>
                        {d.ruleText}
                    </Text>
                    <SegmentedControl value={jurisdiction} onChange={changeJurisdiction} options={data.jurisdictions} fontSize={13} style={{ marginTop: 8 }} />
                    <Checkbox checked={acks.rule} onChange={(rule) => setAcks((a) => ({ ...a, rule }))} label={t.ruleAck} style={{ marginTop: 8 }} />
                </View>

                <View style={styles.card}>
                    <DateField label={t.start} value={start} onChange={changeStart} />
                    <Text size={12.5} color={colors.muted} style={{ marginTop: 4 }}>
                        {t.startHint}
                    </Text>
                    <Checkbox checked={acks.start} onChange={(value) => setAcks((a) => ({ ...a, start: value }))} label={t.startAck} style={{ marginTop: 4 }} />
                </View>

                <View style={styles.card}>
                    <DateField label={t.due} value={due} onChange={changeDue} />
                    <Text size={12.5} color={manual || !due ? colors.proposedText : colors.muted} style={{ marginTop: 4 }}>
                        {dueInfo}
                    </Text>
                    <Checkbox checked={acks.due} onChange={(value) => setAcks((a) => ({ ...a, due: value }))} label={t.dueAck} style={{ marginTop: 4 }} />
                </View>

                <SectionLabel style={{ marginTop: 12 }}>{t.assumptions}</SectionLabel>
                <View style={{ marginTop: 6, gap: 2 }}>
                    {t.assumptionList.map((a) => (
                        <Text key={a} size={13.5} lineHeight={1.45} color={colors.body}>
                            •  {a}
                        </Text>
                    ))}
                </View>

                <SectionLabel style={{ marginTop: 12 }}>{t.reminders}</SectionLabel>
                <View style={{ flexDirection: 'row', gap: 6, marginTop: 6 }}>
                    {[
                        { on: remind3, set: setRemind3, label: t.remind3(formatDayMonth(addDays(reminderBase, -3))) },
                        { on: remind1, set: setRemind1, label: t.remind1(formatDayMonth(addDays(reminderBase, -1))) },
                    ].map((r) => (
                        <Pressable
                            key={r.label}
                            accessibilityRole="switch"
                            accessibilityState={{ checked: r.on }}
                            onPress={() => r.set(!r.on)}
                            style={[styles.reminder, { backgroundColor: r.on ? colors.ink : colors.white, borderColor: r.on ? colors.ink : colors.lineStrong }]}
                        >
                            <Text size={13} weight={600} align="center" color={r.on ? colors.white : colors.ink}>
                                {r.label}
                            </Text>
                        </Pressable>
                    ))}
                </View>
            </ScrollView>
            <StickyActionBar>
                <Button
                    label={checked === 3 ? t.confirm(formatDate(due)) : t.needsChecks(checked)}
                    variant={checked === 3 ? 'confirm' : 'inactive'}
                    busy={busy}
                    onPress={confirm}
                />
                <Button label={t.later} variant="ghost" size="sm" weight={500} onPress={later} style={{ marginTop: 4 }} />
            </StickyActionBar>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    banner: { borderWidth: 1, borderStyle: 'dashed', borderRadius: radius.field, paddingVertical: 10, paddingHorizontal: 12 },
    bannerProposed: { borderColor: colors.proposed, backgroundColor: colors.proposedBg },
    bannerOk: { borderColor: colors.confirmed, backgroundColor: colors.confirmedBg },
    card: { marginTop: 8, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 14, paddingVertical: 12 },
    reminder: { flex: 1, minHeight: 48, borderWidth: 1, borderRadius: radius.field, paddingHorizontal: 8, paddingVertical: 4, alignItems: 'center', justifyContent: 'center' },
});
