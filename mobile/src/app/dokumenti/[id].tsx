import { Image } from 'expo-image';
import { useLocalSearchParams } from 'expo-router';
import { ScrollView, StyleSheet, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { ScreenBar } from '@/components/ui/ScreenBar';
import { ScreenState } from '@/components/ui/ScreenState';
import { Text } from '@/components/ui/Text';
import { API_URL, authHeaders } from '@/lib/api';
import { useScreen } from '@/lib/useScreen';
import { colors, shadow } from '@/theme/tokens';

type DocumentData = {
    document: {
        id: number;
        name: string;
        paragraphs: { text: string; align?: 'center' | 'right' | 'left'; bold?: boolean }[];
        isFictional: boolean;
        fileUrl: string | null;
        isImage: boolean;
    };
};

export default function DocumentScreen() {
    const { id } = useLocalSearchParams<{ id: string }>();
    const { data, error, reload } = useScreen<DocumentData>(`/documents/${id}`);
    const doc = data?.document;

    return (
        <View style={{ flex: 1, backgroundColor: colors.viewer }}>
            <ScreenBar title={doc?.name ?? ''} />
            {!doc ? (
                <ScreenState error={error} onRetry={reload} />
            ) : (
                <ScrollView contentContainerStyle={{ padding: 18 }}>
                    <View style={styles.page}>
                        {doc.paragraphs.map((p, i) => (
                            <Text key={i} family="serif" weight={p.bold ? 600 : 400} size={14.5} lineHeight={1.5} align={p.align ?? 'left'} style={{ marginBottom: 9 }}>
                                {p.text}
                            </Text>
                        ))}
                        {doc.isFictional ? (
                            <Text family="mono" size={11} color={colors.proposedText} style={{ marginTop: 26 }}>
                                {bs.document.fictional}
                            </Text>
                        ) : null}
                    </View>
                    {doc.fileUrl && doc.isImage ? (
                        // The original is fetched with the user's token; never through a public URL.
                        <Image
                            source={{ uri: `${API_URL}${doc.fileUrl}`, headers: authHeaders() }}
                            accessibilityLabel={doc.name}
                            contentFit="contain"
                            style={styles.image}
                        />
                    ) : null}
                </ScrollView>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    page: { backgroundColor: colors.white, paddingHorizontal: 22, paddingTop: 26, paddingBottom: 30, minHeight: 440, ...shadow.page },
    image: { marginTop: 12, width: '100%', aspectRatio: 0.75, backgroundColor: colors.white },
});
