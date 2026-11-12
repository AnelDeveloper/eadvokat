import { useState } from 'react';
import { StyleSheet, TextInput, View } from 'react-native';
import { formatMoney, parseMoney } from '@shared/lib/format';
import { Text } from '@/components/ui/Text';
import { colors, fonts, radius } from '@/theme/tokens';

/** Decimal string ("480.00") in and out; shown as 1.234,56. */
export function MoneyField({ value, onChange, suffix = 'KM', label }: { value: string | null; onChange: (value: string | null) => void; suffix?: string; label?: string }) {
    const [text, setText] = useState(formatMoney(value));
    const [shown, setShown] = useState(value);

    if (value !== shown) {
        setShown(value);

        if (value !== parseMoney(text)) {
            setText(formatMoney(value));
        }
    }

    function commit(): void {
        const parsed = parseMoney(text);
        onChange(parsed);

        if (parsed !== null) {
            setText(formatMoney(parsed));
        }
    }

    return (
        <View style={styles.box}>
            <TextInput
                accessibilityLabel={label}
                value={text}
                onChangeText={setText}
                onEndEditing={commit}
                onBlur={commit}
                keyboardType="decimal-pad"
                style={styles.input}
            />
            <Text size={12} color={colors.muted}>
                {suffix}
            </Text>
        </View>
    );
}

const styles = StyleSheet.create({
    box: {
        flex: 1,
        minHeight: 44,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        paddingHorizontal: 10,
        borderWidth: 1,
        borderColor: colors.lineStrong,
        borderRadius: radius.field,
        backgroundColor: colors.white,
    },
    input: { flex: 1, minWidth: 0, textAlign: 'right', fontFamily: fonts.mono400, fontSize: 15, color: colors.ink, paddingVertical: 8 },
});
