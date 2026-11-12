import { useMemo, useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { WEEKDAYS_SHORT, addDays, formatDate, formatDayMonth, fromIso } from '@shared/lib/format';
import { Button } from '@/components/ui/Button';
import { Checkbox } from '@/components/ui/Checkbox';
import { DateField } from '@/components/ui/DateField';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { ScreenState } from '@/components/ui/ScreenState';
import { SegmentedControl } from '@/components/ui/SegmentedControl';
import { StickyActionBar } from '@/components/ui/StickyActionBar';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { api, ApiError, formData } from '@/lib/api';
import { go } from '@/lib/navigate';
import { type PickedFile, pickDocument, pickPhoto, takePhoto } from '@/lib/pickers';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

type Submission = {
    matters: { id: number; caseNumber: string; title: string; openDeadlines: { id: number; due: string; title: string }[] }[];
    nextNumber: string;
    today: string;
    defaults: { docType: string; reminderText: string };
    deliveryMethods: { value: string; label: string }[];
};

const t = bs.submission;
/** Follow-up reminder offset in days (from the prototype). */
const FOLLOW_UP_DAYS = 10;

export default function SubmissionScreen() {
    const { data, error, reload } = useScreen<Submission>('/submissions/create');

    if (!data) {
        return (
            <View style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenBar title={t.title} />
                <ScreenState error={error} onRetry={reload} />
            </View>
        );
    }

    return <SubmissionForm data={data} />;
}

function SubmissionForm({ data }: { data: Submission }) {
    const toast = useToast();
    const [matterId, setMatterId] = useState(data.matters[0]?.id ?? 0);
    const [docType, setDocType] = useState(data.defaults.docType);
    const [sentOn, setSentOn] = useState<string | null>(data.today);
    const [method, setMethod] = useState('in_person');
    const [proof, setProof] = useState<PickedFile | null>(null);
    const [fulfils, setFulfils] = useState<number[]>([]);
    const [reminderText, setReminderText] = useState(data.defaults.reminderText);
    const [chosenDay, setChosenDay] = useState('');
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [busy, setBusy] = useState(false);

    // When the computed day falls on a weekend, the lawyer picks it or the next Monday.
    const followUp = useMemo(() => {
        const base = sentOn ?? data.today;
        const day = addDays(base, FOLLOW_UP_DAYS);
        const dow = fromIso(day).getDay();
        const weekend = dow === 6 || dow === 0;

        return { base, weekend, accusative: dow === 6 ? 'subotu' : 'nedjelju', options: weekend ? [day, addDays(day, dow === 6 ? 2 : 1)] : [day] };
    }, [sentOn, data.today]);

    // Default to the later option (Monday) unless the lawyer chose one of the offered days.
    const reminderOn = followUp.options.includes(chosenDay) ? chosenDay : followUp.options[followUp.options.length - 1];
    const setReminderOn = setChosenDay;

    const matter = data.matters.find((m) => m.id === matterId);
    const optionLabel = (iso: string) => `${WEEKDAYS_SHORT[fromIso(iso).getDay()]}, ${formatDayMonth(iso)}`;

    async function attachProof(pick: () => Promise<PickedFile | null>): Promise<void> {
        try {
            setProof((await pick()) ?? proof);
        } catch {
            setProof((await pickPhoto()) ?? proof);
        }
    }

    async function save(): Promise<void> {
        setBusy(true);

        try {
            const result = await api.post(
                '/submissions',
                formData({
                    matter_id: matterId,
                    doc_type: docType,
                    sent_on: sentOn,
                    delivery_method: method,
                    proof,
                    fulfils,
                    reminder_text: reminderText,
                    reminder_on: reminderOn,
                }),
            );

            if (result.message) {
                toast(result.message);
            }

            go(result.next, true);
        } catch (e) {
            if (e instanceof ApiError) {
                setErrors(e.errors);
                toast(e.first());
            }
        } finally {
            setBusy(false);
        }
    }

    return (
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1, backgroundColor: colors.app }}>
            <ScreenBar title={t.title} />
            <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingTop: 14, paddingBottom: 18, gap: 10 }} keyboardShouldPersistTaps="handled">
                <Text family="mono" size={12.5} color={colors.muted}>
                    {data.nextNumber} · {matter?.caseNumber}
                </Text>

                {data.matters.length > 1 ? (
                    <View>
                        <Text size={12} weight={500} color={colors.muted}>
                            {t.matter}
                        </Text>
                        <View style={{ gap: 6, marginTop: 4 }}>
                            {data.matters.map((m) => (
                                <Checkbox key={m.id} checked={m.id === matterId} onChange={() => { setMatterId(m.id); setFulfils([]); }} label={`${m.caseNumber} · ${m.title}`} />
                            ))}
                        </View>
                    </View>
                ) : null}

                <TextField label={t.docType} value={docType} onChangeText={setDocType} placeholder={t.docTypePlaceholder} error={errors.doc_type?.[0]} />
                <DateField label={t.sentOn} value={sentOn} onChange={setSentOn} large={false} error={errors.sent_on?.[0]} />

                <View>
                    <Text size={12} weight={500} color={colors.muted}>
                        {t.method}
                    </Text>
                    <SegmentedControl value={method} onChange={setMethod} options={data.deliveryMethods} style={{ marginTop: 4 }} />
                </View>

                <View style={{ marginTop: 4 }}>
                    <Text size={12} weight={500} color={colors.muted}>
                        {t.proof}
                    </Text>
                    <Pressable
                        accessibilityRole="button"
                        onPress={() => void attachProof(takePhoto)}
                        style={[styles.proof, proof ? styles.proofOn : styles.proofOff]}
                    >
                        <View style={styles.thumb}>
                            <Text family="mono" size={9} align="center" color={proof ? colors.confirmedText : colors.ink}>
                                {proof ? t.proofThumb(formatDayMonth(sentOn ?? data.today)) : '+'}
                            </Text>
                        </View>
                        <View style={{ flex: 1 }}>
                            <Text size={14.5} weight={600} color={proof ? colors.confirmedText : colors.ink}>
                                {proof ? t.proofAttached : t.proofEmpty}
                            </Text>
                            <Text size={12.5} color={colors.muted}>
                                {proof ? proof.name : t.proofEmptySub}
                            </Text>
                        </View>
                    </Pressable>
                    <View style={{ flexDirection: 'row', gap: 16 }}>
                        <Pressable accessibilityRole="button" onPress={() => void attachProof(pickPhoto)} style={styles.link}>
                            <Text size={13} weight={600} style={{ textDecorationLine: 'underline' }}>
                                {bs.mobile.chooseFromLibrary}
                            </Text>
                        </Pressable>
                        <Pressable accessibilityRole="button" onPress={() => void attachProof(pickDocument)} style={styles.link}>
                            <Text size={13} weight={600} style={{ textDecorationLine: 'underline' }}>
                                PDF
                            </Text>
                        </Pressable>
                    </View>
                    {errors.proof ? (
                        <Text size={12} color={colors.urgentText}>
                            {errors.proof[0]}
                        </Text>
                    ) : null}
                </View>

                {(matter?.openDeadlines ?? []).map((d) => (
                    <Checkbox
                        key={d.id}
                        checked={fulfils.includes(d.id)}
                        onChange={(on) => setFulfils(on ? [...fulfils, d.id] : fulfils.filter((x) => x !== d.id))}
                        label={t.fulfils(d.due)}
                    />
                ))}

                <View style={styles.reminder}>
                    <Text size={16} weight={600}>
                        {t.reminder}
                    </Text>
                    <TextField value={reminderText} onChangeText={setReminderText} accessibilityLabel={t.reminder} fontSize={14.5} error={errors.reminder_text?.[0]} style={{ marginTop: 4 }} />
                    {followUp.weekend ? (
                        <>
                            <Text size={12.5} color={colors.proposedText} style={{ marginTop: 6 }}>
                                {t.weekendNote(formatDate(followUp.base), followUp.accusative)}
                            </Text>
                            <SegmentedControl
                                value={reminderOn}
                                onChange={setReminderOn}
                                options={followUp.options.map((d) => ({ value: d, label: optionLabel(d) }))}
                                style={{ marginTop: 5 }}
                            />
                        </>
                    ) : (
                        <Text size={12.5} color={colors.muted} style={{ marginTop: 6 }}>
                            {t.reminderOn(optionLabel(reminderOn || followUp.options[0]))}
                        </Text>
                    )}
                </View>
            </ScrollView>
            <StickyActionBar>
                <Button label={t.save} busy={busy} onPress={save} />
            </StickyActionBar>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    proof: { marginTop: 4, minHeight: 64, flexDirection: 'row', alignItems: 'center', gap: 12, borderRadius: radius.field, paddingHorizontal: 12, paddingVertical: 10, borderWidth: 1 },
    proofOff: { borderStyle: 'dashed', borderColor: colors.faint, backgroundColor: colors.white },
    proofOn: { borderColor: colors.confirmed, backgroundColor: colors.confirmedBg },
    thumb: { width: 40, height: 48, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: 3, backgroundColor: colors.white, alignItems: 'center', justifyContent: 'center' },
    link: { minHeight: 44, justifyContent: 'center' },
    reminder: { marginTop: 4, paddingTop: 14, borderTopWidth: 2, borderTopColor: colors.ink },
});
