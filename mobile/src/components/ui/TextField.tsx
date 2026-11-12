import { StyleSheet, TextInput, type TextInputProps, View, type ViewStyle } from 'react-native';
import { Text } from '@/components/ui/Text';
import { colors, fonts, radius } from '@/theme/tokens';

export function TextField({
    label,
    note,
    error,
    warn = false,
    mono = false,
    fontSize = 15,
    style,
    ...input
}: TextInputProps & { label?: string; note?: string; error?: string; warn?: boolean; mono?: boolean; fontSize?: number; style?: ViewStyle }) {
    return (
        <View style={style}>
            {label ? (
                <Text size={12} weight={500} color={colors.muted}>
                    {label}
                </Text>
            ) : null}
            <TextInput
                accessibilityLabel={label}
                placeholderTextColor={colors.faint}
                {...input}
                style={[
                    styles.input,
                    { borderColor: warn ? colors.proposed : colors.lineStrong, fontFamily: mono ? fonts.mono400 : fonts.sans400, fontSize },
                    input.multiline && styles.multiline,
                ]}
            />
            {note ? (
                <Text size={12} color={colors.proposedText} style={{ marginTop: 3 }}>
                    {note}
                </Text>
            ) : null}
            {error ? (
                <Text size={12} color={colors.urgentText} style={{ marginTop: 3 }}>
                    {error}
                </Text>
            ) : null}
        </View>
    );
}

const styles = StyleSheet.create({
    input: {
        marginTop: 4,
        minHeight: 44,
        borderWidth: 1,
        borderRadius: radius.field,
        backgroundColor: colors.white,
        paddingHorizontal: 12,
        color: colors.ink,
    },
    multiline: { paddingTop: 10, paddingBottom: 10, minHeight: 96, textAlignVertical: 'top', lineHeight: 20 },
});
