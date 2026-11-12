import { router, useLocalSearchParams } from 'expo-router';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { addDays } from '@shared/lib/format';
import { Button } from '@/components/ui/Button';
import { DateField } from '@/components/ui/DateField';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { api, ApiError } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

const t = bs.newDeadline;
type Data = {
    matter: { id: number; caseNumber: string; title: string };
    entry: { id: number; number: string; docType: string; receivedOn: string; documentId: number | null } | null;
    today: string;
};

export default function NewDeadlineScreen() {
    const { id, unos } = useLocalSearchParams<{ id: string; unos?: string }>();
    const { data, error, reload } = useScreen<Data>(`/matters/${id}/deadlines/create${unos ? `?unos=${unos}` : ''}`);

    if (!data) {
        return (
            <SafeAreaView style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenState error={error} onRetry={reload} />
            </SafeAreaView>
        );
    }

    return <DeadlineForm data={data} />;
}

function DeadlineForm({ data }: { data: Data }) {
    const toast = useToast();
    const [title, setTitle] = useState('');
    const [rule, setRule] = useState('');
    const [days, setDays] = useState('');
    const [start, setStart] = useState<string | null>(data.entry?.receivedOn ?? data.today);
    const [due, setDue] = useState<string | null>(null);
    const [quote, setQuote] = useState('');
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [busy, setBusy] = useState(false);

    const n = Number(days);
    const validDays = Number.isInteger(n) && n > 0 ? n : null;

    // Rule 3: last day = start + N calendar days (receipt day not counted); the lawyer can change it.
    function recompute(nextStart: string | null, nextDays: number | null): void {
        if (nextStart && nextDays) {
            setDue(addDays(nextStart, nextDays));
        }
    }

    async function submit(): Promise<void> {
        setBusy(true);

        try {
            const result = await api.post(`/matters/${data.matter.id}/deadlines`, {
                title,
                rule_text: rule,
                days: validDays,
                start_date: start,
                due_date: due,
                source_quote: quote,
                mail_entry_id: data.entry?.id ?? null,
            });
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
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.app }}>
            <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingBottom: 24 }} keyboardShouldPersistTaps="handled">
                    <Pressable onPress={() => go(`/predmeti/${data.matter.id}`, true)} accessibilityRole="link" style={styles.back}>
                        <Text size={15} weight={500}>
                            ‹ {data.matter.caseNumber}
                        </Text>
                    </Pressable>
                    <Text size={30} weight={600} lineHeight={1.1} accessibilityRole="header">
                        {t.title}
                    </Text>
                    <Text size={14} color={colors.muted} style={{ marginTop: 4 }}>
                        {data.matter.title}
                    </Text>
                    {data.entry ? (
                        <Text family="mono" size={12.5} color={colors.muted} style={{ marginTop: 4 }}>
                            {t.fromEntry(data.entry.number, data.entry.docType)}
                        </Text>
                    ) : null}
                    <View style={styles.notice}>
                        <Text size={13.5} lineHeight={1.4} color={colors.proposedText}>
                            {t.intro}
                        </Text>
                    </View>
                    <View style={styles.card}>
                        <TextField label={t.name} placeholder={t.namePlaceholder} value={title} onChangeText={setTitle} error={errors.title?.[0]} />
                        <TextField label={t.rule} placeholder={t.rulePlaceholder} value={rule} onChangeText={setRule} error={errors.rule_text?.[0]} />
                        <DateField
                            label={t.start}
                            value={start}
                            onChange={(value) => {
                                setStart(value);
                                recompute(value, validDays);
                            }}
                            error={errors.start_date?.[0]}
                        />
                        <TextField
                            label={t.days}
                            value={days}
                            keyboardType="number-pad"
                            mono
                            onChangeText={(value) => {
                                setDays(value);
                                const parsed = Number(value);
                                recompute(start, Number.isInteger(parsed) && parsed > 0 ? parsed : null);
                            }}
                            error={errors.days?.[0]}
                        />
                        <DateField label={t.due} value={due} onChange={setDue} error={errors.due_date?.[0]} />
                        <Text size={12.5} color={colors.muted}>
                            {t.computed}
                        </Text>
                        <TextField label={t.quote} value={quote} onChangeText={setQuote} multiline />
                        <Button label={t.submit} busy={busy} onPress={submit} />
                        <Button label={t.skip} variant="ghost" size="sm" weight={500} onPress={() => router.replace(`/predmeti/${data.matter.id}` as never)} />
                    </View>
                </ScrollView>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    back: { height: 44, justifyContent: 'center', alignSelf: 'flex-start' },
    notice: { marginTop: 12, borderWidth: 1, borderStyle: 'dashed', borderColor: colors.proposed, backgroundColor: colors.proposedBg, borderRadius: radius.field, paddingHorizontal: 12, paddingVertical: 10 },
    card: { marginTop: 12, gap: 12, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 14, paddingVertical: 16 },
});
