import { router } from 'expo-router';
import { useState } from 'react';
import { Pressable, RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { ScreenState } from '@/components/ui/ScreenState';
import { SegmentedControl } from '@/components/ui/SegmentedControl';
import { StickyActionBar } from '@/components/ui/StickyActionBar';
import { Text } from '@/components/ui/Text';
import { go } from '@/lib/navigate';
import { useScreen } from '@/lib/useScreen';
import { colors } from '@/theme/tokens';

type Mail = {
    direction: 'in' | 'out';
    entries: { id: number; number: string; date: string; title: string; sub: string; link: string | null; linkTone: 'confirmed' | 'proposed' | null; documentId: number | null }[];
};

export default function MailScreen() {
    const [direction, setDirection] = useState<'in' | 'out'>('in');
    const { data, error, loading, reload } = useScreen<Mail>(direction === 'out' ? '/mail?smjer=izlazna' : '/mail');

    return (
        <SafeAreaView edges={['top']} style={{ flex: 1, backgroundColor: colors.app }}>
            <ScrollView contentContainerStyle={{ paddingHorizontal: 18, paddingTop: 6, paddingBottom: 18 }} refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} />}>
                <Text size={30} weight={600} lineHeight={1.1} style={{ marginTop: 8, marginBottom: 12 }} accessibilityRole="header">
                    {bs.mail.title}
                </Text>
                <SegmentedControl
                    value={direction}
                    onChange={setDirection}
                    fontSize={14.5}
                    options={[
                        { value: 'in', label: bs.mail.incoming },
                        { value: 'out', label: bs.mail.outgoing },
                    ]}
                />
                {!data || data.direction !== direction ? (
                    <View style={{ minHeight: 200 }}>
                        <ScreenState error={error} onRetry={reload} />
                    </View>
                ) : (
                    <View style={styles.list}>
                        {data.entries.map((entry) => (
                            <Pressable
                                key={entry.id}
                                disabled={!entry.documentId}
                                onPress={() => go(`/dokumenti/${entry.documentId}`)}
                                accessibilityRole={entry.documentId ? 'button' : undefined}
                                style={styles.entry}
                            >
                                <View style={styles.between}>
                                    <Text family="mono" size={12.5} color={colors.muted}>
                                        {entry.number}
                                    </Text>
                                    <Text family="mono" size={12.5} color={colors.muted}>
                                        {entry.date}
                                    </Text>
                                </View>
                                <Text size={15.5} weight={500} lineHeight={1.3} style={{ marginTop: 3 }}>
                                    {entry.title}
                                </Text>
                                <Text size={12.5} color={colors.muted} style={{ marginTop: 2 }}>
                                    {entry.sub}
                                </Text>
                                {entry.link ? (
                                    <Text size={12.5} color={entry.linkTone === 'confirmed' ? colors.confirmedText : colors.proposedText} style={{ marginTop: 2 }}>
                                        {entry.link}
                                    </Text>
                                ) : null}
                                {entry.documentId ? (
                                    <Text size={13} weight={600} style={{ marginTop: 6, textDecorationLine: 'underline' }}>
                                        {bs.common.sourceDocument}
                                    </Text>
                                ) : null}
                            </Pressable>
                        ))}
                        {data.entries.length === 0 ? (
                            <Text size={14} color={colors.muted} style={{ paddingVertical: 12 }}>
                                {bs.mail.empty}
                            </Text>
                        ) : null}
                    </View>
                )}
            </ScrollView>
            <StickyActionBar aboveTabs>
                <Button
                    label={direction === 'in' ? bs.mail.captureIncoming : bs.mail.recordOutgoing}
                    onPress={() => router.push(direction === 'in' ? '/posta/fotografisi' : '/posta/podnesak')}
                />
            </StickyActionBar>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    list: { marginTop: 12, borderTopWidth: 2, borderTopColor: colors.ink },
    entry: { paddingVertical: 12, paddingHorizontal: 2, borderBottomWidth: 1, borderBottomColor: colors.line },
    between: { flexDirection: 'row', justifyContent: 'space-between' },
});
