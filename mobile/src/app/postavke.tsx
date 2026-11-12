import { ScrollView, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { Text } from '@/components/ui/Text';
import { API_URL } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { colors, radius } from '@/theme/tokens';

export default function SettingsScreen() {
    const { user, signOut } = useAuth();

    return (
        <View style={{ flex: 1, backgroundColor: colors.app }}>
            <ScreenBar title={bs.settings.title} />
            <ScrollView contentContainerStyle={{ padding: 18 }}>
                <View style={styles.card}>
                    <SectionLabel>{bs.settings.profile}</SectionLabel>
                    <Text size={12} color={colors.muted} style={{ marginTop: 10 }}>
                        {bs.mobile.signedInAs}
                    </Text>
                    <Text size={16} weight={600}>
                        {user?.displayName}
                    </Text>
                    <Text size={14} color={colors.muted}>
                        {user?.email}
                    </Text>
                    <Text size={12} color={colors.muted} style={{ marginTop: 10 }}>
                        {bs.mobile.server}
                    </Text>
                    <Text family="mono" size={12.5}>
                        {API_URL}
                    </Text>
                </View>
                <Button label={bs.settings.logout} variant="subtle" size="sm" weight={500} onPress={() => void signOut()} style={{ marginTop: 14 }} />
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    card: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, paddingHorizontal: 14, paddingVertical: 12 },
});
