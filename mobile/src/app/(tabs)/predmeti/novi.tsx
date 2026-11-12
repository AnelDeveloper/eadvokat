import { router } from 'expo-router';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { SegmentedControl } from '@/components/ui/SegmentedControl';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { api, ApiError } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

const t = bs.newMatter;
type Form = { case_number: string; title: string; subject: string; court: string; represented_side: string; client_name: string; jurisdiction: string };

export default function NewMatterScreen() {
    const { data } = useScreen<{ jurisdictions: { value: string; label: string }[] }>('/matters/create');
    const toast = useToast();
    const [form, setForm] = useState<Form>({ case_number: '', title: '', subject: '', court: '', represented_side: '', client_name: '', jurisdiction: 'fbih' });
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [busy, setBusy] = useState(false);
    const set = (patch: Partial<Form>) => setForm({ ...form, ...patch });

    async function submit(): Promise<void> {
        setBusy(true);

        try {
            const result = await api.post('/matters', form);

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
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.app }}>
            <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingBottom: 24 }} keyboardShouldPersistTaps="handled">
                    <Pressable onPress={() => router.navigate('/predmeti')} accessibilityRole="link" style={styles.back}>
                        <Text size={15} weight={500}>
                            {bs.matters.back}
                        </Text>
                    </Pressable>
                    <Text size={30} weight={600} lineHeight={1.1} accessibilityRole="header">
                        {t.title}
                    </Text>
                    <View style={styles.card}>
                        <TextField label={t.caseNumber} placeholder={t.caseNumberPlaceholder} mono value={form.case_number} onChangeText={(case_number) => set({ case_number })} error={errors.case_number?.[0]} />
                        <TextField label={t.name} placeholder={t.namePlaceholder} value={form.title} onChangeText={(title) => set({ title })} error={errors.title?.[0]} />
                        <TextField label={t.subject} placeholder={t.subjectPlaceholder} value={form.subject} onChangeText={(subject) => set({ subject })} error={errors.subject?.[0]} />
                        <TextField label={t.court} placeholder={t.courtPlaceholder} value={form.court} onChangeText={(court) => set({ court })} error={errors.court?.[0]} />
                        <TextField label={t.side} placeholder={t.sidePlaceholder} value={form.represented_side} onChangeText={(represented_side) => set({ represented_side })} error={errors.represented_side?.[0]} />
                        <TextField label={t.client} value={form.client_name} onChangeText={(client_name) => set({ client_name })} error={errors.client_name?.[0]} />
                        <View>
                            <Text size={12} weight={500} color={colors.muted}>
                                {t.jurisdiction}
                            </Text>
                            <SegmentedControl value={form.jurisdiction} onChange={(jurisdiction) => set({ jurisdiction })} options={data?.jurisdictions ?? []} style={{ marginTop: 4 }} />
                        </View>
                        <Button label={t.submit} busy={busy} onPress={submit} />
                    </View>
                </ScrollView>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    back: { height: 44, justifyContent: 'center', alignSelf: 'flex-start' },
    card: { marginTop: 14, gap: 12, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 14, paddingVertical: 16 },
});
