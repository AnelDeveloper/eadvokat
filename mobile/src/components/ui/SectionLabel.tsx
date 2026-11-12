import { View, type ViewStyle } from 'react-native';
import { Text } from '@/components/ui/Text';
import { colors } from '@/theme/tokens';

export function SectionLabel({ children, band = false, style }: { children: string; band?: boolean; style?: ViewStyle }) {
    return (
        <View
            style={[
                band && {
                    backgroundColor: colors.app,
                    borderTopWidth: 1,
                    borderTopColor: colors.line,
                    paddingHorizontal: 14,
                    paddingTop: 9,
                    paddingBottom: 5,
                },
                style,
            ]}
        >
            <Text size={11.5} weight={600} color={colors.muted} uppercase tracking={0.06} accessibilityRole="header">
                {children}
            </Text>
        </View>
    );
}
