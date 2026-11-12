import { RecordingPresets, requestRecordingPermissionsAsync, setAudioModeAsync, useAudioRecorder, useAudioRecorderState } from 'expo-audio';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { Button } from '@/components/ui/Button';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { api, ApiError, formData } from '@/lib/api';
import { go } from '@/lib/navigate';
import { useToast } from '@/lib/toast';
import { useScreen } from '@/lib/useScreen';
import { colors } from '@/theme/tokens';

type Dictation = { hearing: { id: number; label: string } | null; isDemo: boolean; available: boolean };

const t = bs.dictation;

export default function DictationScreen() {
    const { data, error, reload } = useScreen<Dictation>('/dictation');
    const recorder = useAudioRecorder(RecordingPresets.HIGH_QUALITY);
    const state = useAudioRecorderState(recorder, 500);
    const toast = useToast();
    const [busy, setBusy] = useState(false);
    const [micDenied, setMicDenied] = useState(false);

    const seconds = Math.floor((state.durationMillis ?? 0) / 1000);
    const clock = `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`;

    async function send(uri: string | null): Promise<void> {
        if (!data?.hearing) {
            return;
        }

        setBusy(true);

        try {
            const result = await api.post(
                '/dictation',
                formData({
                    event_id: data.hearing.id,
                    audio: uri ? { uri, name: 'diktat.m4a', type: 'audio/mp4' } : null,
                }),
            );
            go(result.next, true);
        } catch (e) {
            toast(e instanceof ApiError ? e.first() : bs.mobile.genericError);
        } finally {
            setBusy(false);
        }
    }

    async function toggle(): Promise<void> {
        if (busy) {
            return;
        }

        if (state.isRecording) {
            await recorder.stop();
            await send(recorder.uri);

            return;
        }

        const permission = await requestRecordingPermissionsAsync();

        if (!permission.granted) {
            setMicDenied(true);

            return;
        }

        setMicDenied(false);
        await setAudioModeAsync({ allowsRecording: true, playsInSilentMode: true });
        await recorder.prepareToRecordAsync();
        recorder.record();
    }

    return (
        <View style={{ flex: 1, backgroundColor: colors.app }}>
            <ScreenBar title={t.title} />
            {!data ? (
                <ScreenState error={error} onRetry={reload} />
            ) : (
                <View style={styles.body}>
                    {!data.available ? (
                        <Text size={15} color={colors.muted} align="center" style={{ maxWidth: 320 }}>
                            {t.unavailable}
                        </Text>
                    ) : data.hearing ? (
                        <>
                            <Text family="mono" size={12.5} color={colors.muted} align="center">
                                {data.hearing.label}
                            </Text>
                            <Pressable
                                accessibilityRole="button"
                                accessibilityLabel={t.micLabel}
                                accessibilityState={{ selected: state.isRecording, busy }}
                                onPress={() => void toggle()}
                                style={[styles.mic, { backgroundColor: state.isRecording ? colors.urgent : colors.ink }]}
                            >
                                <View style={{ alignItems: 'center' }}>
                                    <View style={styles.capsule} />
                                    <View style={styles.cup} />
                                    <View style={styles.stem} />
                                </View>
                            </Pressable>
                            <View style={styles.status} accessibilityLiveRegion="polite">
                                {state.isRecording ? <View style={styles.dot} /> : null}
                                <Text size={18} weight={600}>
                                    {busy ? t.processing : state.isRecording ? t.recording(clock) : t.idle}
                                </Text>
                            </View>
                            <Text size={14} color={colors.muted} align="center" style={{ maxWidth: 280 }}>
                                {state.isRecording ? (data.isDemo ? t.recordingHint : t.recordingHintReal) : t.idleHint}
                            </Text>
                            {micDenied ? (
                                <Text size={14} color={colors.urgentText} align="center" style={{ maxWidth: 300 }}>
                                    {bs.mobile.micDenied}
                                </Text>
                            ) : null}
                            {micDenied && data.isDemo ? <Button label={t.useFixture} variant="outline" size="sm" onPress={() => void send(null)} /> : null}
                        </>
                    ) : (
                        <Text size={15} color={colors.muted} align="center">
                            {t.noHearing}
                        </Text>
                    )}
                </View>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    body: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 18, padding: 24 },
    mic: { width: 112, height: 112, borderRadius: 56, alignItems: 'center', justifyContent: 'center' },
    capsule: { width: 20, height: 34, borderRadius: 10, backgroundColor: colors.white },
    cup: { width: 34, height: 12, marginTop: -8, borderWidth: 3, borderTopWidth: 0, borderColor: colors.white, borderBottomLeftRadius: 17, borderBottomRightRadius: 17 },
    stem: { width: 3, height: 8, backgroundColor: colors.white },
    status: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    dot: { width: 10, height: 10, borderRadius: 5, backgroundColor: colors.urgent },
});
