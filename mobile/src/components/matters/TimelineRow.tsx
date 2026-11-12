import { Pressable, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import type { AgendaRow } from '@shared/types/agenda';
import type { DisplayStatus } from '@shared/types/status';
import { StatusChip } from '@/components/ui/StatusChip';
import { Text } from '@/components/ui/Text';
import { go } from '@/lib/navigate';
import { colors } from '@/theme/tokens';

// Dot on the rail; a proposal is a white dot with a dashed amber ring.
const dot: Record<DisplayStatus, { bg: string; border: string; dashed?: boolean }> = {
    proposed: { bg: colors.white, border: colors.proposed, dashed: true },
    confirmed: { bg: colors.confirmed, border: colors.confirmed },
    urgent: { bg: colors.urgent, border: colors.urgent },
    recorded: { bg: colors.faint, border: colors.lineStrong },
    fulfilled: { bg: colors.faint, border: colors.lineStrong },
};

export function TimelineRow({ row }: { row: AgendaRow }) {
    const d = dot[row.status];

    return (
        <View style={styles.row}>
            <View style={{ width: 88 }}>
                <Text family="mono" size={12}>
                    {row.when}
                </Text>
                {row.time ? (
                    <Text family="mono" size={12} color={colors.muted}>
                        {row.time}
                    </Text>
                ) : null}
            </View>
            <View style={styles.rail}>
                <View style={styles.line} />
                <View style={[styles.dot, { backgroundColor: d.bg, borderColor: d.border, borderStyle: d.dashed ? 'dashed' : 'solid' }]} />
            </View>
            <View style={styles.body}>
                <Text size={11} weight={600} color={colors.muted} uppercase tracking={0.05}>
                    {row.type}
                </Text>
                <Pressable disabled={!row.href} onPress={() => go(row.href)} accessibilityRole={row.href ? 'link' : undefined}>
                    <Text size={15} weight={500} lineHeight={1.3}>
                        {row.title}
                    </Text>
                </Pressable>
                <Text size={12.5} color={colors.muted} style={{ marginTop: 2 }}>
                    {row.sub}
                </Text>
                <StatusChip status={row.status} style={{ marginTop: 6 }} />
                {row.documentId ? (
                    <Pressable onPress={() => go(`/dokumenti/${row.documentId}`)} accessibilityRole="link" style={styles.doc}>
                        <Text size={13} weight={600} style={{ textDecorationLine: 'underline' }}>
                            {bs.common.sourceDocument}
                        </Text>
                    </Pressable>
                ) : null}
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    row: { flexDirection: 'row', gap: 8 },
    rail: { width: 12 },
    line: { position: 'absolute', left: 5, top: 0, bottom: 0, width: 1, backgroundColor: colors.lineStrong },
    dot: { position: 'absolute', left: 1, top: 4, width: 9, height: 9, borderRadius: 5, borderWidth: 1 },
    body: { flex: 1, minWidth: 0, paddingBottom: 16 },
    doc: { minHeight: 44, justifyContent: 'center', alignSelf: 'flex-start' },
});
