import { Pressable, StyleSheet, View } from 'react-native';
import type { DisplayStatus } from '@shared/types/status';
import { StatusChip } from '@/components/ui/StatusChip';
import { Text } from '@/components/ui/Text';
import { colors } from '@/theme/tokens';

export function ListRow({
    when,
    title,
    sub,
    type,
    status,
    label,
    onPress,
    chevron = true,
    divider = 'top',
}: {
    when: string;
    title: string;
    sub?: string | null;
    type?: string | null;
    status?: DisplayStatus;
    label?: string;
    onPress?: () => void;
    chevron?: boolean;
    divider?: 'top' | 'bottom';
}) {
    return (
        <Pressable
            accessibilityRole={onPress ? 'button' : undefined}
            disabled={!onPress}
            onPress={onPress}
            style={({ pressed }) => [
                styles.row,
                divider === 'top' ? styles.top : styles.bottom,
                { backgroundColor: pressed ? colors.app : colors.white },
            ]}
        >
            <Text family="mono" weight={500} size={13} style={styles.when}>
                {when}
            </Text>
            <View style={styles.body}>
                {type ? (
                    <Text size={11} weight={600} color={colors.muted} uppercase tracking={0.05}>
                        {type}
                    </Text>
                ) : null}
                <Text size={15} weight={500} lineHeight={1.3}>
                    {title}
                </Text>
                {sub ? (
                    <Text size={12.5} color={colors.muted} style={{ marginTop: 2 }}>
                        {sub}
                    </Text>
                ) : null}
                {status ? <StatusChip status={status} label={label} style={{ marginTop: 6 }} /> : null}
            </View>
            {chevron && (
                <Text size={18} color={colors.faint} style={{ width: 10 }}>
                    ›
                </Text>
            )}
        </Pressable>
    );
}

const styles = StyleSheet.create({
    row: { minHeight: 56, flexDirection: 'row', gap: 10, paddingVertical: 10, paddingHorizontal: 14, alignItems: 'flex-start' },
    top: { borderTopWidth: 1, borderTopColor: colors.hairline },
    bottom: { borderBottomWidth: 1, borderBottomColor: colors.hairline },
    when: { width: 52, paddingTop: 2 },
    body: { flex: 1, minWidth: 0 },
});
