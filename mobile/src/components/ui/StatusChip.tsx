import { StyleSheet, View, type ViewStyle } from 'react-native';
import { bs } from '@shared/i18n/bs';
import type { DisplayStatus } from '@shared/types/status';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

// Dashed amber always means "not active yet"; a proposal is never solid or green.
const tone: Record<DisplayStatus, { fg: string; bg: string; border: string; dashed?: boolean }> = {
    proposed: { fg: colors.proposedText, bg: colors.proposedBg, border: colors.proposed, dashed: true },
    confirmed: { fg: colors.confirmedText, bg: colors.confirmedBg, border: colors.confirmed },
    urgent: { fg: colors.urgentText, bg: colors.urgentBg, border: colors.urgent },
    recorded: { fg: colors.muted, bg: colors.neutralBg, border: colors.lineStrong },
    fulfilled: { fg: colors.muted, bg: colors.neutralBg, border: colors.lineStrong },
};

export function StatusChip({ status, label, style }: { status: DisplayStatus; label?: string; style?: ViewStyle }) {
    const t = tone[status];

    return (
        <View
            style={[
                styles.chip,
                { backgroundColor: t.bg, borderColor: t.border, borderStyle: t.dashed ? 'dashed' : 'solid' },
                style,
            ]}
        >
            <Text size={11} weight={600} color={t.fg} style={{ lineHeight: 13 }}>
                {label ?? bs.status[status]}
            </Text>
        </View>
    );
}

const styles = StyleSheet.create({
    chip: {
        alignSelf: 'flex-start',
        borderWidth: 1,
        borderRadius: radius.chip,
        paddingHorizontal: 7,
        paddingVertical: 3,
    },
});
