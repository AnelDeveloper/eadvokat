import { Image } from 'expo-image';
import { Text, View } from 'react-native';
import { bs } from '@shared/i18n/bs';
import { colors, fonts } from '@/theme/tokens';

/** Ročište logo: calendar mark + wordmark. */
export function Logo({ height = 30 }: { height?: number }) {
    return (
        <View
            accessible
            accessibilityRole="image"
            accessibilityLabel={bs.appName}
            style={{ flexDirection: 'row', alignItems: 'center', gap: height * 0.3 }}
        >
            <Image
                source={require('@/assets/images/mark.png')}
                style={{ width: height, height, borderRadius: height * 0.23 }}
            />
            <Text
                style={{
                    fontFamily: fonts.serif600,
                    fontSize: height * 0.86,
                    color: colors.ink,
                    letterSpacing: -0.2,
                }}
            >
                {bs.appName}
            </Text>
        </View>
    );
}
