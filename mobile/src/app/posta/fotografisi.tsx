import { router } from 'expo-router';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Text } from '@/components/ui/Text';
import { api, ApiError, formData } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { go } from '@/lib/navigate';
import { type PickedFile, pickDocument, pickPhoto, takePhoto } from '@/lib/pickers';
import { useToast } from '@/lib/toast';
import { colors } from '@/theme/tokens';

const t = bs.capture;

export default function CaptureScreen() {
    const { user } = useAuth();
    const toast = useToast();
    const [busy, setBusy] = useState(false);

    async function upload(pick: () => Promise<PickedFile | null>): Promise<void> {
        if (busy) {
            return;
        }

        let file: PickedFile | null;

        try {
            file = await pick();
        } catch (e) {
            if (e instanceof Error && e.message === 'denied') {
                toast(bs.mobile.cameraDenied);

                return;
            }

            // No camera (e.g. a simulator): fall back to the photo library.
            file = await pickPhoto();
        }

        if (!file) {
            return;
        }

        setBusy(true);

        try {
            const result = await api.post('/mail/capture', formData({ file }));
            go(result.next, true);
        } catch (e) {
            toast(e instanceof ApiError ? e.first() : bs.mobile.genericError);
        } finally {
            setBusy(false);
        }
    }

    return (
        <SafeAreaView style={styles.page}>
            <View style={styles.top}>
                <Pressable onPress={() => router.back()} accessibilityRole="button" style={styles.cancel}>
                    <Text size={15} color={colors.white}>
                        {t.cancel}
                    </Text>
                </Pressable>
                <Text size={13} color={colors.lineStrong} style={{ paddingRight: 8 }}>
                    {t.heading}
                </Text>
            </View>

            <View style={styles.stage}>
                <View style={styles.paper}>
                    {user?.isDemo ? (
                        <>
                            <Text family="serif" weight={600} size={10.5} align="center" color={colors.ink}>
                                OPĆINSKI SUD U SARAJEVU
                            </Text>
                            <Text family="serif" size={10.5} color={colors.ink} style={{ marginTop: 8 }}>
                                Broj: 65 0 P 084512 25 P{'\n'}Sarajevo, 06.11.2026. godine
                            </Text>
                            <Text family="serif" weight={600} size={13} align="center" color={colors.ink} style={{ marginVertical: 12, letterSpacing: 1.8 }}>
                                RJEŠENJE
                            </Text>
                            <Text family="serif" size={10.5} lineHeight={1.45} color={colors.ink}>
                                Tužiocu se, putem punomoćnika, dostavlja nalaz i mišljenje vještaka finansijske struke od 30.10.2026. godine.
                            </Text>
                            <Text family="serif" size={10.5} lineHeight={1.45} color={colors.ink} style={{ marginTop: 7 }}>
                                Poziva se tužilac da se u roku od 15 dana od dana prijema ovog rješenja pismeno izjasni na nalaz i mišljenje vještaka.
                            </Text>
                            <Text family="serif" size={10.5} color={colors.ink} style={{ marginTop: 7 }}>
                                Protiv ovog rješenja nije dozvoljena posebna žalba.
                            </Text>
                            <Text family="serif" size={10.5} align="right" color={colors.ink} style={{ marginTop: 18 }}>
                                Sudija
                            </Text>
                            <Text family="mono" size={8.5} color={colors.proposedText} style={{ marginTop: 14 }}>
                                {t.fictional}
                            </Text>
                        </>
                    ) : null}
                </View>
                {busy ? (
                    <View style={styles.overlay} accessibilityLiveRegion="polite">
                        <Text size={16} weight={500} color={colors.white}>
                            {t.reading}
                        </Text>
                    </View>
                ) : null}
            </View>

            <Text size={13} color={colors.lineStrong} align="center" style={{ paddingHorizontal: 30 }}>
                {user?.isDemo ? t.hint : t.hintReal}
            </Text>
            <View style={styles.shutterRow}>
                <Pressable accessibilityRole="button" accessibilityLabel={t.shutter} onPress={() => void upload(takePhoto)} style={styles.shutter}>
                    <View style={styles.shutterInner} />
                </Pressable>
            </View>
            <View style={styles.alternatives}>
                <Pressable accessibilityRole="button" onPress={() => void upload(pickPhoto)} style={styles.alt}>
                    <Text size={14} weight={500} color={colors.white} style={{ textDecorationLine: 'underline' }}>
                        {bs.mobile.chooseFromLibrary}
                    </Text>
                </Pressable>
                <Pressable accessibilityRole="button" onPress={() => void upload(pickDocument)} style={styles.alt}>
                    <Text size={14} weight={500} color={colors.white} style={{ textDecorationLine: 'underline' }}>
                        {bs.mobile.chooseFile}
                    </Text>
                </Pressable>
            </View>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    page: { flex: 1, backgroundColor: colors.inkDeep },
    top: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 12 },
    cancel: { height: 44, justifyContent: 'center', paddingHorizontal: 8 },
    stage: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 34, paddingVertical: 10 },
    paper: {
        width: '100%',
        maxWidth: 360,
        aspectRatio: 1 / 1.36,
        backgroundColor: colors.white,
        borderRadius: 3,
        paddingVertical: 22,
        paddingHorizontal: 20,
        transform: [{ rotate: '-1.2deg' }],
        borderWidth: 2,
        borderColor: colors.proposed,
    },
    overlay: { position: "absolute", top: 0, left: 0, right: 0, bottom: 0, backgroundColor: colors.overlay, alignItems: 'center', justifyContent: 'center' },
    shutterRow: { alignItems: 'center', paddingTop: 16, paddingBottom: 10 },
    shutter: { width: 74, height: 74, borderRadius: 37, borderWidth: 4, borderColor: colors.white, padding: 4 },
    shutterInner: { flex: 1, borderRadius: 33, backgroundColor: colors.white },
    alternatives: { flexDirection: 'row', justifyContent: 'center', gap: 18, paddingBottom: 12 },
    alt: { minHeight: 44, justifyContent: 'center' },
});
