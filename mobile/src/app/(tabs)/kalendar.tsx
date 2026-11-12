import { useState } from 'react';
import { Pressable, RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import type { AgendaRow } from '@shared/types/agenda';
import { ListRow } from '@/components/ui/ListRow';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { go } from '@/lib/navigate';
import { useScreen } from '@/lib/useScreen';
import { colors, radius } from '@/theme/tokens';

type Marker = 'event' | 'deadline' | 'urgent' | 'proposed' | 'muted';
type Calendar = {
    month: string;
    monthLabel: string;
    prevMonth: string;
    nextMonth: string;
    offset: number;
    days: { day: number; isToday: boolean; markers: Marker[] }[];
    selectedDay: number;
    selectedLabel: string;
    rows: AgendaRow[];
};

// ink = hearing/meeting, green = confirmed deadline, red = urgent, hollow amber = proposal, grey = reminder/past.
function markerStyle(marker: Marker, selected: boolean) {
    if (marker === 'proposed') {
        return { borderWidth: 1.5, borderColor: selected ? colors.proposedBg : colors.proposed, backgroundColor: colors.transparent };
    }

    const fill = { event: colors.ink, deadline: colors.confirmed, urgent: colors.urgent, muted: colors.faint }[marker];

    return { backgroundColor: selected && marker !== 'muted' ? colors.white : fill };
}

export default function CalendarScreen() {
    const [query, setQuery] = useState('');
    const { data, error, loading, reload } = useScreen<Calendar>(`/calendar${query}`);

    if (!data) {
        return (
            <SafeAreaView style={{ flex: 1, backgroundColor: colors.app }}>
                <ScreenState error={error} onRetry={reload} />
            </SafeAreaView>
        );
    }

    const cells: ((typeof data.days)[number] | null)[] = [...Array<null>(data.offset).fill(null), ...data.days];

    return (
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.app }}>
            <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingTop: 6, paddingBottom: 18 }} refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}>
                <View style={styles.head}>
                    <Text size={26} weight={600} lineHeight={1.1} accessibilityRole="header">
                        {data.monthLabel}
                    </Text>
                    <View style={{ flexDirection: 'row', gap: 6 }}>
                        {[
                            { label: '‹', a11y: bs.calendar.prev, month: data.prevMonth },
                            { label: '›', a11y: bs.calendar.next, month: data.nextMonth },
                        ].map((b) => (
                            <Pressable key={b.label} accessibilityRole="button" accessibilityLabel={b.a11y} onPress={() => setQuery(`?mjesec=${b.month}`)} style={styles.nav}>
                                <Text size={20}>{b.label}</Text>
                            </Pressable>
                        ))}
                    </View>
                </View>

                <View style={[styles.grid, { marginTop: 10 }]}>
                    {bs.calendar.weekdays.map((d) => (
                        <View key={d} style={styles.cellWidth}>
                            <Text size={11.5} weight={600} color={colors.muted} align="center">
                                {d}
                            </Text>
                        </View>
                    ))}
                </View>
                <View style={[styles.grid, { marginTop: 4, rowGap: 2 }]}>
                    {cells.map((d, i) =>
                        d === null ? (
                            <View key={`blank-${i}`} style={styles.cellWidth} />
                        ) : (
                            <View key={d.day} style={styles.cellWidth}>
                                <Pressable
                                    accessibilityRole="button"
                                    accessibilityState={{ selected: d.day === data.selectedDay }}
                                    onPress={() => setQuery(`?mjesec=${data.month}&dan=${d.day}`)}
                                    style={[
                                        styles.cell,
                                        d.day === data.selectedDay
                                            ? { backgroundColor: colors.ink, borderColor: colors.ink }
                                            : { borderColor: d.isToday ? colors.ink : colors.transparent },
                                    ]}
                                >
                                    <Text size={15} weight={d.isToday ? 600 : 400} color={d.day === data.selectedDay ? colors.white : colors.ink}>
                                        {d.day}
                                    </Text>
                                    <View style={styles.markers}>
                                        {d.markers.map((m, k) => (
                                            <View key={k} style={[styles.marker, markerStyle(m, d.day === data.selectedDay)]} />
                                        ))}
                                    </View>
                                </Pressable>
                            </View>
                        ),
                    )}
                </View>

                <View style={styles.legend}>
                    {[
                        { label: bs.calendar.legend.event, style: { backgroundColor: colors.ink } },
                        { label: bs.calendar.legend.deadline, style: { backgroundColor: colors.confirmed } },
                        { label: bs.calendar.legend.proposed, style: { borderWidth: 1.5, borderColor: colors.proposed } },
                        { label: bs.calendar.legend.reminder, style: { backgroundColor: colors.faint } },
                    ].map((l) => (
                        <View key={l.label} style={{ flexDirection: 'row', alignItems: 'center', gap: 5 }}>
                            <View style={[{ width: 7, height: 7, borderRadius: 4 }, l.style]} />
                            <Text size={12} color={colors.muted}>
                                {l.label}
                            </Text>
                        </View>
                    ))}
                </View>

                <View style={styles.day}>
                    <View style={styles.dayHead}>
                        <Text size={15} weight={600}>
                            {data.selectedLabel}
                        </Text>
                    </View>
                    {data.rows.length === 0 ? (
                        <Text size={14} color={colors.muted} style={{ padding: 14 }}>
                            {bs.calendar.empty}
                        </Text>
                    ) : null}
                    {data.rows.map((row) => (
                        <ListRow
                            key={row.id}
                            when={row.when}
                            type={row.type}
                            title={row.title}
                            sub={row.sub}
                            status={row.status}
                            chevron={false}
                            divider="bottom"
                            onPress={row.href ? () => go(row.href) : undefined}
                        />
                    ))}
                </View>
            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    head: { marginTop: 6, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    nav: { width: 44, height: 44, borderWidth: 1, borderColor: colors.lineStrong, borderRadius: radius.field, backgroundColor: colors.white, alignItems: 'center', justifyContent: 'center' },
    grid: { flexDirection: 'row', flexWrap: 'wrap' },
    cellWidth: { width: `${100 / 7}%`, paddingHorizontal: 1 },
    cell: { height: 46, borderRadius: radius.field, borderWidth: 1, alignItems: 'center', justifyContent: 'center', gap: 4 },
    markers: { flexDirection: 'row', gap: 3, height: 6 },
    marker: { width: 6, height: 6, borderRadius: 3 },
    legend: { flexDirection: 'row', flexWrap: 'wrap', columnGap: 12, rowGap: 4, marginTop: 10 },
    day: { marginTop: 14, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, borderRadius: radius.card, overflow: 'hidden' },
    dayHead: { paddingHorizontal: 14, paddingVertical: 10, borderBottomWidth: 2, borderBottomColor: colors.ink },
});
