import { router, useLocalSearchParams } from 'expo-router';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { formatDate } from '@shared/lib/format';
import { Button } from '@/components/ui/Button';
import { Checkbox } from '@/components/ui/Checkbox';
import { DateField } from '@/components/ui/DateField';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { ScreenState } from '@/components/ui/ScreenState';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { StatusChip } from '@/components/ui/StatusChip';
import { StickyActionBar } from '@/components/ui/StickyActionBar';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { api, ApiError } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

type Saved = {
    event: { id: number; date: string; time: string; room: string | null; confirmed: boolean } | null;
    deadline: { id: number; dueDate: string; status: string; confirmed: boolean } | null;
    note: { id: number; body: string; confirmed: boolean } | null;
};
type Result = {
    documentId: number;
    proposal: {
        transcript: string;
        event: { title: string; date: string; time: string; heard: string; options: string[] };
        deadline: { title: string; ruleText: string; highlight: string; days: number; startDate: string; dueDate: string };
        note: string;
        hearingDate: string;
    };
    saved: Saved | null;
};

const t = bs.dictation;

function Card({ label, confirmed, title, children }: { label: string; confirmed: boolean; title?: string; children?: React.ReactNode }) {
    return (
        <View style={styles.card}>
            <View style={styles.between}>
                <Text size={12} weight={500} color={colors.muted}>
                    {label}
                </Text>
                <StatusChip status={confirmed ? 'confirmed' : 'proposed'} />
            </View>
            {title ? (
                <Text size={15.5} weight={500} style={{ marginTop: 3 }}>
                    {title}
                </Text>
            ) : null}
            {children}
        </View>
    );
}

function Toggle({ on, onPress, disabled }: { on: boolean; onPress: () => void; disabled?: boolean }) {
    return (
        <Pressable
            accessibilityRole="button"
            disabled={disabled}
            onPress={onPress}
            style={[styles.toggle, { backgroundColor: on ? colors.white : colors.ink }]}
        >
            <Text size={14} weight={600} color={on ? colors.ink : colors.white}>
                {on ? t.confirmed : t.confirm}
            </Text>
        </Pressable>
    );
}

export default function DictationResultScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, reload } = useScreen<Result>(`/dictation/${id}`);

    if (!data) {
        return (
            <View style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenBar title={t.title} />
                <ScreenState error={error} onRetry={reload} />
            </View>
        );
    }

    // Re-mount the form when the saved state changes (after confirming a card).
    return <ResultForm key={JSON.stringify(data.saved)} id={id} data={data} reload={reload} />;
}

function ResultForm({ id, data, reload }: { id: string; data: Result; reload: () => Promise<void> }) {
    const toast = useToast();
    const [busy, setBusy] = useState(false);
    const s = data.saved;

    // Draft: every card is an independent proposal; editing a field resets it.
    const [event, setEvent] = useState({
        date: (s?.event?.date ?? data.proposal.event.date) as string | null,
        time: s?.event?.time ?? data.proposal.event.time,
        room: (s?.event?.room ?? null) as string | null,
        confirmed: false,
    });
    const [deadline, setDeadline] = useState({ due: data.proposal.deadline.dueDate as string | null, rule: false, start: false, dueAck: false, confirmed: false });
    const [note, setNote] = useState({ body: s?.note?.body ?? data.proposal.note, confirmed: false });

    const p = data.proposal;
    const saved = data.saved;
    const resolved = event.room !== null;
    const shownRoom = event.room ?? p.event.heard;
    const acks = deadline.rule && deadline.start && deadline.dueAck;
    const count = Number(event.confirmed) + Number(deadline.confirmed) + Number(note.confirmed);

    const token = `sudnica ${p.event.heard}`;
    const at = p.transcript.indexOf(token);
    const before = at < 0 ? p.transcript : `${p.transcript.slice(0, at)}sudnica `;
    const after = at < 0 ? '' : p.transcript.slice(at + token.length);

    function toggleEvent(): void {
        if (!event.confirmed && !resolved) {
            toast(t.roomFirst(p.event.options));

            return;
        }

        setEvent({ ...event, confirmed: !event.confirmed });
    }

    function toggleDeadline(): void {
        if (!deadline.confirmed && !acks) {
            toast(bs.deadline.needsChecksToast);

            return;
        }

        setDeadline({ ...deadline, confirmed: !deadline.confirmed });
    }

    async function run(action: () => Promise<{ message: string | null; next: string | null }>): Promise<void> {
        setBusy(true);

        try {
            const result = await action();

            if (result.message) {
                toast(result.message);
            }

            if (result.next) {
                go(result.next, true);
            } else {
                await reload();
            }
        } catch (e) {
            toast(e instanceof ApiError ? e.first() : bs.mobile.genericError);
        } finally {
            setBusy(false);
        }
    }

    function save(): void {
        if (!event.date || !deadline.due) {
            toast(bs.common.dateHint);

            return;
        }

        void run(() =>
            api.post(`/dictation/${id}/save`, {
                event: { date: event.date, time: event.time, location: event.room, confirmed: event.confirmed },
                deadline: { due_date: deadline.due, rule_ack: deadline.rule, start_ack: deadline.start, due_ack: deadline.dueAck, confirmed: deadline.confirmed },
                note: { body: note.body, confirmed: note.confirmed },
            }),
        );
    }

    const eventConfirmed = saved ? !!saved.event?.confirmed : event.confirmed;
    const deadlineConfirmed = saved ? !!saved.deadline?.confirmed : deadline.confirmed;
    const noteConfirmed = saved ? !!saved.note?.confirmed : note.confirmed;

    return (
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1, backgroundColor: colors.app }}>
            <ScreenBar title={t.title} onBack={() => router.replace('/danas')} />
            <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingTop: 14, paddingBottom: 18 }} keyboardShouldPersistTaps="handled">
                <SectionLabel>{t.transcript}</SectionLabel>
                <View style={styles.transcript}>
                    <Text family="serif" size={15.5} lineHeight={1.45}>
                        „{before}
                        {at >= 0 ? (
                            <Text family="serif" size={15.5} style={resolved ? undefined : styles.mark}>
                                {shownRoom}
                            </Text>
                        ) : null}
                        {after}“
                    </Text>
                </View>

                <Card label={t.eventCard} confirmed={eventConfirmed} title={t.eventTitle(p.event.title)}>
                    {!saved || !eventConfirmed ? (
                        <>
                            <View style={{ flexDirection: 'row', gap: 6, marginTop: 8 }}>
                                <DateField label={t.date} value={event.date} onChange={(date) => setEvent({ ...event, date, confirmed: false })} large={false} style={{ flex: 1.5 }} />
                                <TextField label={t.time} value={event.time} onChangeText={(time) => setEvent({ ...event, time, confirmed: false })} mono style={{ flex: 1 }} />
                            </View>
                            <Text size={12.5} color={resolved ? colors.confirmedText : colors.proposedText} style={{ marginTop: 8 }}>
                                {resolved ? t.roomChecked(shownRoom) : t.roomUnclear(p.event.options)}
                            </Text>
                            <View style={{ flexDirection: 'row', gap: 6, marginTop: 5 }}>
                                {p.event.options.map((room) => {
                                    const on = event.room === room;

                                    return (
                                        <Pressable
                                            key={room}
                                            accessibilityRole="button"
                                            accessibilityState={{ selected: on }}
                                            onPress={() => setEvent({ ...event, room, confirmed: false })}
                                            style={[styles.room, { backgroundColor: on ? colors.ink : colors.white, borderColor: on ? colors.ink : colors.lineStrong }]}
                                        >
                                            <Text size={14} weight={600} color={on ? colors.white : colors.ink}>
                                                {t.room(room)}
                                            </Text>
                                        </Pressable>
                                    );
                                })}
                            </View>
                            {!saved ? (
                                <Toggle on={event.confirmed} onPress={toggleEvent} />
                            ) : (
                                <Toggle
                                    on={false}
                                    disabled={busy}
                                    onPress={() =>
                                        resolved
                                            ? void run(() => api.post(`/events/${saved.event?.id}/confirm`, { date: event.date, time: event.time, location: event.room }))
                                            : toast(t.roomFirst(p.event.options))
                                    }
                                />
                            )}
                        </>
                    ) : null}
                </Card>

                <Card label={t.deadlineCard} confirmed={deadlineConfirmed} title={p.deadline.title}>
                    <Text size={12.5} color={colors.muted} style={{ marginTop: 2 }}>
                        {t.deadlineSource(p.deadline.highlight, formatDate(p.deadline.startDate))}
                    </Text>
                    {!saved ? (
                        <>
                            <DateField label={t.dueLabel} value={deadline.due} onChange={(due) => setDeadline({ ...deadline, due, dueAck: false, confirmed: false })} large={false} style={{ marginTop: 8 }} />
                            <Text size={12} weight={500} color={colors.muted} style={{ marginTop: 8 }}>
                                {p.deadline.ruleText}
                            </Text>
                            <Checkbox checked={deadline.rule} onChange={(rule) => setDeadline({ ...deadline, rule, confirmed: rule && deadline.confirmed })} label={bs.deadline.ruleAck} />
                            <Checkbox checked={deadline.start} onChange={(start) => setDeadline({ ...deadline, start, confirmed: start && deadline.confirmed })} label={bs.deadline.startAck} />
                            <Checkbox checked={deadline.dueAck} onChange={(dueAck) => setDeadline({ ...deadline, dueAck, confirmed: dueAck && deadline.confirmed })} label={bs.deadline.dueAck} />
                            <Toggle on={deadline.confirmed} onPress={toggleDeadline} />
                        </>
                    ) : saved.deadline && !saved.deadline.confirmed ? (
                        <Button label={t.reviewDeadline} size="sm" onPress={() => go(`/rokovi/${saved.deadline?.id}/prijedlog`)} style={{ marginTop: 8 }} />
                    ) : null}
                </Card>

                <Card label={t.noteCard} confirmed={noteConfirmed}>
                    {!saved || !noteConfirmed ? (
                        <>
                            <TextField
                                value={note.body}
                                onChangeText={(body) => setNote({ body, confirmed: false })}
                                multiline
                                accessibilityLabel={t.noteCard}
                                fontSize={14.5}
                                style={{ marginTop: 4 }}
                            />
                            {!saved ? (
                                <Toggle on={note.confirmed} onPress={() => setNote({ ...note, confirmed: !note.confirmed })} />
                            ) : (
                                <Toggle on={false} disabled={busy} onPress={() => void run(() => api.post(`/notes/${saved.note?.id}/confirm`, { body: note.body }))} />
                            )}
                        </>
                    ) : (
                        <Text size={14.5} lineHeight={1.4} style={{ marginTop: 8 }}>
                            {saved.note?.body}
                        </Text>
                    )}
                </Card>

                <Text size={12.5} color={colors.muted} style={{ marginTop: 10 }}>
                    {saved ? t.savedHint : t.costHint(p.hearingDate)}
                </Text>
            </ScrollView>
            {!saved ? (
                <StickyActionBar>
                    <Button label={t.save(count)} busy={busy} onPress={save} />
                    <Text size={12} color={colors.muted} align="center" style={{ marginTop: 6 }}>
                        {t.saveHint}
                    </Text>
                </StickyActionBar>
            ) : null}
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    transcript: { marginTop: 6, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: radius.chip, paddingHorizontal: 14, paddingVertical: 12 },
    mark: { backgroundColor: colors.proposedBg, textDecorationLine: 'underline', textDecorationColor: colors.proposed },
    card: { marginTop: 10, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 14, paddingVertical: 12 },
    between: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: 8 },
    room: { flex: 1, height: 44, borderWidth: 1, borderRadius: radius.field, alignItems: 'center', justifyContent: 'center' },
    toggle: { marginTop: 8, height: 44, borderWidth: 1, borderColor: colors.ink, borderRadius: radius.field, alignItems: 'center', justifyContent: 'center' },
});
