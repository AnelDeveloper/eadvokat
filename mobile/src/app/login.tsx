import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { Text } from '@/components/ui/Text';
import { TextField } from '@/components/ui/TextField';
import { ApiError } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { colors, radius } from '@/theme/tokens';

export default function Login() {
    const { signIn } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState<string | undefined>();

    async function submit(): Promise<void> {
        setBusy(true);
        setError(undefined);

        try {
            await signIn(email, password);
        } catch (e) {
            setError(e instanceof ApiError ? e.first() : bs.mobile.genericError);
            setPassword('');
        } finally {
            setBusy(false);
        }
    }

    return (
        <SafeAreaView style={{ flex: 1, backgroundColor: colors.app }}>
            <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ flex: 1 }}>
                <ScrollView contentContainerStyle={styles.page} keyboardShouldPersistTaps="handled">
                    <Text family="serif" weight={600} size={24} style={{ marginBottom: 24 }}>
                        {bs.appName}
                    </Text>
                    <View style={styles.card}>
                        <Text size={30} weight={600} accessibilityRole="header">
                            {bs.auth.loginTitle}
                        </Text>
                        <Text size={14} color={colors.muted} style={{ marginTop: 6 }}>
                            {bs.auth.loginDescription}
                        </Text>
                        <View style={{ gap: 12, marginTop: 16 }}>
                            <TextField
                                label={bs.auth.email}
                                value={email}
                                onChangeText={setEmail}
                                autoCapitalize="none"
                                autoComplete="email"
                                keyboardType="email-address"
                                textContentType="username"
                                error={error}
                            />
                            <TextField
                                label={bs.auth.password}
                                value={password}
                                onChangeText={setPassword}
                                secureTextEntry
                                autoComplete="current-password"
                                textContentType="password"
                                onSubmitEditing={submit}
                            />
                            <Button label={bs.auth.submit} onPress={submit} busy={busy} />
                        </View>
                    </View>
                    <Text size={13} color={colors.muted} style={{ marginTop: 14 }}>
                        {bs.auth.noAccount}
                    </Text>
                </ScrollView>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    page: { flexGrow: 1, justifyContent: 'center', padding: 20 },
    card: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 18, paddingVertical: 20 },
});
