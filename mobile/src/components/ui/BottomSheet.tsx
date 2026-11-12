import type { ReactNode } from 'react';
import { Modal, Pressable, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

export function BottomSheet({ open, onClose, title, children }: { open: boolean; onClose: () => void; title: string; children: ReactNode }) {
    const insets = useSafeAreaInsets();

    return (
        <Modal visible={open} transparent animationType="slide" onRequestClose={onClose}>
            <Pressable style={styles.scrim} onPress={onClose} accessibilityLabel={title}>
                <Pressable style={[styles.sheet, { paddingBottom: 26 + insets.bottom }]} onPress={() => {}}>
                    <Text size={18} weight={600}>
                        {title}
                    </Text>
                    <View>{children}</View>
                </Pressable>
            </Pressable>
        </Modal>
    );
}

const styles = StyleSheet.create({
    scrim: { flex: 1, backgroundColor: colors.scrim, justifyContent: 'flex-end' },
    sheet: {
        backgroundColor: colors.white,
        borderTopLeftRadius: radius.sheet,
        borderTopRightRadius: radius.sheet,
        paddingTop: 20,
        paddingHorizontal: 18,
    },
});
