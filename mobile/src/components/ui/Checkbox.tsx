import { Pressable, StyleSheet, View, type ViewStyle } from 'react-native';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

export function Checkbox({ checked, onChange, label, style }: { checked: boolean; onChange: (value: boolean) => void; label: string; style?: ViewStyle }) {
    return (
        <Pressable
            accessibilityRole="checkbox"
            accessibilityState={{ checked }}
            onPress={() => onChange(!checked)}
            style={[styles.row, style]}
        >
            <View
                style={[
                    styles.box,
                    { backgroundColor: checked ? colors.confirmed : colors.white, borderColor: checked ? colors.confirmed : colors.faint },
                ]}
            >
                {checked && (
                    <Text size={15} color={colors.white} style={{ lineHeight: 18 }}>
                        ✓
                    </Text>
                )}
            </View>
            <Text size={14.5} weight={500} style={{ flex: 1 }}>
                {label}
            </Text>
        </Pressable>
    );
}

const styles = StyleSheet.create({
    row: { minHeight: 44, flexDirection: 'row', alignItems: 'center', gap: 10 },
    box: { width: 24, height: 24, borderRadius: radius.checkbox, borderWidth: 2, alignItems: 'center', justifyContent: 'center' },
});
