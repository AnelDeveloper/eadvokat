import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { Text } from '@/components/ui/Text';
import type { ApiError } from '@/lib/api';
import { colors } from '@/theme/tokens';

/** Loading spinner or an error with retry, shown until a screen's data arrives. */
export function ScreenState({ error, onRetry }: { error: ApiError | null; onRetry: () => void }) {
    return (
        <View style={styles.box}>
            {error ? (
                <>
                    <Text size={15} color={colors.muted} align="center">
                        {error.message}
                    </Text>
                    <Button label={bs.mobile.retry} variant="outline" size="sm" onPress={onRetry} style={{ marginTop: 14 }} />
                </>
            ) : (
                <ActivityIndicator color={colors.ink} accessibilityLabel={bs.mobile.loading} />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    box: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 24 },
});
