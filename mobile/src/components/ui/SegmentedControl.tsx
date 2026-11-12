import { Pressable, StyleSheet, View, type ViewStyle } from 'react-native';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

export function SegmentedControl<T extends string>({
    value,
    onChange,
    options,
    fontSize = 13.5,
    tall = false,
    style,
}: {
    value: T;
    onChange: (value: T) => void;
    options: readonly { value: T; label: string }[];
    fontSize?: number;
    tall?: boolean;
    style?: ViewStyle;
}) {
    return (
        <View accessibilityRole="radiogroup" style={[styles.row, style]}>
            {options.map((option) => {
                const selected = option.value === value;

                return (
                    <Pressable
                        key={option.value}
                        accessibilityRole="radio"
                        accessibilityState={{ selected }}
                        onPress={() => onChange(option.value)}
                        style={[
                            styles.item,
                            { minHeight: tall ? 48 : 44, backgroundColor: selected ? colors.ink : colors.white, borderColor: selected ? colors.ink : colors.lineStrong },
                        ]}
                    >
                        <Text size={fontSize} weight={600} color={selected ? colors.white : colors.ink} align="center">
                            {option.label}
                        </Text>
                    </Pressable>
                );
            })}
        </View>
    );
}

const styles = StyleSheet.create({
    row: { flexDirection: 'row', gap: 6 },
    item: { flex: 1, borderWidth: 1, borderRadius: radius.field, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 6 },
});
