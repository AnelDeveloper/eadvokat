import { Pressable, StyleSheet, View, type ViewStyle } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

/** A quoted passage from a source document; the app quotes and never interprets. */
export function SourceQuote({
    text,
    highlight,
    variant = 'source',
    note,
    meta,
    onOpen,
    style,
}: {
    text: string;
    highlight?: string | null;
    variant?: 'source' | 'excerpt';
    note?: string;
    meta?: string;
    onOpen?: () => void;
    style?: ViewStyle;
}) {
    const at = highlight ? text.indexOf(highlight) : -1;
    const parts = at < 0 || !highlight ? [text, '', ''] : [text.slice(0, at), highlight, text.slice(at + highlight.length)];
    const source = variant === 'source';

    return (
        <View style={[source ? styles.source : styles.excerpt, style]}>
            <Text family="serif" italic={!source} size={source ? 16 : 15} lineHeight={1.4}>
                „{parts[0]}
                {parts[1] ? (
                    <Text family="serif" size={16} style={styles.mark}>
                        {parts[1]}
                    </Text>
                ) : null}
                {parts[2]}“
            </Text>
            {note ? (
                <Text size={12.5} color={colors.proposedText} style={{ marginTop: 5 }}>
                    {note}
                </Text>
            ) : null}
            {meta || onOpen ? (
                <View style={styles.meta}>
                    <Text family="mono" size={12} color={colors.muted} style={{ flex: 1 }}>
                        {meta}
                    </Text>
                    {onOpen ? (
                        <Pressable onPress={onOpen} accessibilityRole="link" style={styles.open}>
                            <Text size={13} weight={600} style={{ textDecorationLine: 'underline' }}>
                                {bs.common.open}
                            </Text>
                        </Pressable>
                    ) : null}
                </View>
            ) : null}
        </View>
    );
}

const styles = StyleSheet.create({
    source: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: radius.chip, paddingVertical: 12, paddingHorizontal: 14 },
    excerpt: { backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.field, paddingVertical: 10, paddingHorizontal: 12 },
    mark: { backgroundColor: colors.proposedBg, textDecorationLine: 'underline', textDecorationColor: colors.proposed },
    meta: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 8, marginTop: 6 },
    open: { minHeight: 44, justifyContent: 'center' },
});
