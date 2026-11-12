import { StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import type { BriefingGroup, BriefingRow } from '@shared/types/today';
import { ListRow } from '@/components/ui/ListRow';
import { SectionLabel } from '@/components/ui/SectionLabel';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

export function BriefingCard({ lawyer, groups, onRow }: { lawyer: string; groups: BriefingGroup[]; onRow: (row: BriefingRow) => void }) {
    return (
        <View style={styles.card} accessibilityLabel={bs.today.briefing}>
            <View style={styles.head}>
                <Text size={16} weight={600}>
                    {bs.today.briefing}
                </Text>
                <Text family="mono" size={12} color={colors.muted}>
                    {lawyer}
                </Text>
            </View>
            {groups.map((group) => (
                <View key={group.key}>
                    <SectionLabel band>{bs.today.groups[group.key]}</SectionLabel>
                    {group.key === 'deadlines' && group.rows.length === 0 ? (
                        <View style={styles.note}>
                            <Text size={13.5} color={colors.muted}>
                                {bs.today.noDeadlines}
                            </Text>
                        </View>
                    ) : null}
                    {group.rows.map((row) => (
                        <ListRow
                            key={row.id}
                            when={row.when}
                            title={row.title}
                            sub={row.sub}
                            status={row.status}
                            label={row.label}
                            onPress={row.href || row.toggleUrl ? () => onRow(row) : undefined}
                        />
                    ))}
                </View>
            ))}
        </View>
    );
}

const styles = StyleSheet.create({
    card: { marginTop: 14, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, overflow: 'hidden' },
    head: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'baseline', paddingHorizontal: 14, paddingVertical: 11, borderBottomWidth: 2, borderBottomColor: colors.ink },
    note: { borderTopWidth: 1, borderTopColor: colors.hairline, paddingHorizontal: 14, paddingVertical: 10 },
});
