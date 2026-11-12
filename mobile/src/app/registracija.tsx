import { router } from 'expo-router';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { Logo } from '@/components/ui/Logo';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { ApiError } from '@/lib/api';
import { type RegisterInput, useAuth } from '@/lib/auth';
import { colors, radius } from '@/theme/tokens';

const t = bs.register;

export default function RegisterScreen() {
    const { register } = useAuth();
    const [form, setForm] = useState<RegisterInput>({ office_name: '', title: 'adv.', name: '', email: '', password: '', password_confirmation: '' });
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [busy, setBusy] = useState(false);
    const set = (patch: Partial<RegisterInput>) => setForm({ ...form, ...patch });

    async function submit(): Promise<void> {
        setBusy(true);

        try {
            await register(form);
            router.replace('/predmeti/novi');
        } catch (e) {
            setErrors(e instanceof ApiError ? e.errors : {});
        } finally {
            setBusy(false);
        }
    }

    return (
        <SafeAreaView style={{ flex: 1, backgroundColor: colors.app }}>
            <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                <ScrollView contentContainerStyle={styles.page} keyboardShouldPersistTaps="handled">
                    <View style={{ marginBottom: 20 }}>
                        <Logo height={30} />
                    </View>
                    <View style={styles.card}>
                        <Text size={26} weight={600} accessibilityRole="header">
                            {t.title}
                        </Text>
                        <Text size={14} color={colors.muted} style={{ marginTop: 6 }}>
                            {t.description}
                        </Text>
                        <View style={{ gap: 12, marginTop: 16 }}>
                            <TextField label={t.officeName} placeholder={t.officePlaceholder} value={form.office_name} onChangeText={(office_name) => set({ office_name })} error={errors.office_name?.[0]} />
                            <View style={{ flexDirection: 'row', gap: 8 }}>
                                <TextField label={t.titleLabel} value={form.title} onChangeText={(title) => set({ title })} style={{ width: 96 }} />
                                <TextField label={t.name} value={form.name} onChangeText={(name) => set({ name })} autoComplete="name" error={errors.name?.[0]} style={{ flex: 1 }} />
                            </View>
                            <TextField label={t.email} value={form.email} onChangeText={(email) => set({ email })} autoCapitalize="none" keyboardType="email-address" autoComplete="email" error={errors.email?.[0]} />
                            <TextField label={t.password} value={form.password} onChangeText={(password) => set({ password })} secureTextEntry autoComplete="new-password" error={errors.password?.[0]} />
                            <TextField label={t.confirm} value={form.password_confirmation} onChangeText={(password_confirmation) => set({ password_confirmation })} secureTextEntry autoComplete="new-password" />
                            <Button label={t.submit} busy={busy} onPress={submit} />
                        </View>
                    </View>
                    <View style={styles.loginRow}>
                        <Text size={14} color={colors.muted}>
                            {t.haveAccount}
                        </Text>
                        <Button label={t.login} variant="ghost" size="sm" onPress={() => router.replace('/login')} />
                    </View>
                </ScrollView>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    page: { flexGrow: 1, justifyContent: 'center', padding: 20 },
    card: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 18, paddingVertical: 20 },
    loginRow: { flexDirection: 'row', alignItems: 'center', gap: 2, marginTop: 10 },
});
