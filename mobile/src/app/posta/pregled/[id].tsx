import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { DateField } from '@/components/ui/DateField';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { ScreenState } from '@/components/ui/ScreenState';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { SourceQuote } from '@/components/ui/SourceQuote';
import { StatusChip } from '@/components/ui/StatusChip';
import { StickyActionBar } from '@/components/ui/StickyActionBar';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { api, ApiError } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

type Fields = { court: string; case_number: string; doc_type: string; document_date: string | null; received_on: string | null };
type Review = {
    documentId: number;
    paragraphs: { text: string; align?: 'center' | 'right' | 'left'; bold?: boolean; highlighted?: boolean }[];
    quotes: { text: string; note: string }[];
    registerNumber: string;
    fields: Fields;
    receivedOnAssumed: boolean;
};

const t = bs.review;

export default function MailReviewScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, reload } = useScreen<Review>(`/mail/review/${id}`);
    const toast = useToast();

    useEffect(() => {
        // Already registered: the API answers 409 with the next path.
        if (error?.status === 409) {
            const body = error.body as { message?: string; next?: string } | null;
            toast(body?.message ?? error.message);
            go(body?.next ?? '/posta', true);
        }
    }, [error, toast]);

    if (!data) {
        return (
            <View style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenBar title={t.title} step={t.step} />
                <ScreenState error={error?.status === 409 ? null : error} onRetry={reload} />
            </View>
        );
    }

    return <ReviewForm id={id} data={data} />;
}

function ReviewForm({ id, data }: { id: string; data: Review }) {
    const toast = useToast();
    const [form, setForm] = useState<Fields>(data.fields);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [busy, setBusy] = useState(false);

    const assumed = data.receivedOnAssumed && form.received_on === data.fields.received_on;
    const set = (patch: Partial<Fields>) => setForm({ ...form, ...patch });

    async function save(): Promise<void> {
        setBusy(true);

        try {
            const result = await api.post(`/mail/review/${id}`, form);

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
            <ScreenBar title={t.title} step={t.step} onBack={() => router.replace('/posta')} />
            <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingTop: 14, paddingBottom: 18 }} keyboardShouldPersistTaps="handled">
                <SectionLabel>{t.source}</SectionLabel>
                <ScrollView style={styles.paper} nestedScrollEnabled>
                    {data.paragraphs.map((p, i) => (
                        <View key={i} style={[{ marginBottom: 7 }, p.highlighted && styles.highlight]}>
                            <Text family="serif" weight={p.bold ? 600 : 400} size={13.5} lineHeight={1.45} align={p.align ?? 'left'}>
                                {p.text}
                            </Text>
                        </View>
                    ))}
                </ScrollView>
                <Text size={12} color={colors.muted} style={{ marginTop: 5 }}>
                    {t.sourceHint}
                </Text>

                <View style={styles.heading}>
                    <Text size={17} weight={600}>
                        {t.heading}
                    </Text>
                    <StatusChip status="proposed" />
                </View>
                <Text family="mono" size={12.5} color={colors.muted} style={{ marginTop: 3 }}>
                    {data.registerNumber} · {t.incoming}
                </Text>

                <View style={{ gap: 10, marginTop: 10 }}>
                    <TextField label={t.court} value={form.court} onChangeText={(court) => set({ court })} error={errors.court?.[0]} />
                    <TextField label={t.caseNumber} value={form.case_number} onChangeText={(case_number) => set({ case_number })} error={errors.case_number?.[0]} />
                    <TextField label={t.docType} value={form.doc_type} onChangeText={(doc_type) => set({ doc_type })} error={errors.doc_type?.[0]} />
                    <DateField label={t.documentDate} value={form.document_date} onChange={(document_date) => set({ document_date })} mono={false} large={false} error={errors.document_date?.[0]} />
                    <DateField
                        label={t.receivedOn}
                        value={form.received_on}
                        onChange={(received_on) => set({ received_on })}
                        mono={false}
                        large={false}
                        warn={assumed}
                        note={assumed ? t.receivedAssumed : undefined}
                        error={errors.received_on?.[0]}
                    />
                </View>

                <SectionLabel style={{ marginTop: 14 }}>{t.quotes}</SectionLabel>
                {data.quotes.map((q, i) => (
                    <SourceQuote key={i} variant="excerpt" text={q.text} note={q.note} style={{ marginTop: i === 0 ? 6 : 8 }} />
                ))}
            </ScrollView>
            <StickyActionBar>
                <Button label={t.save} onPress={save} busy={busy} />
            </StickyActionBar>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    paper: { marginTop: 6, maxHeight: 210, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: radius.chip, paddingHorizontal: 16, paddingVertical: 14 },
    highlight: { backgroundColor: colors.proposedBg, borderBottomWidth: 1, borderBottomColor: colors.proposed },
    heading: { marginTop: 16, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
});
