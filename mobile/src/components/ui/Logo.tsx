import { Image } from 'expo-image';
import { bs } from '@shared/i18n/bs';

/** The eAdvokat logo from the brand kit (cropped artwork 1900 × 396). */
export function Logo({ height = 30 }: { height?: number }) {
    return (
        <Image
            source={require('@/assets/images/logo.png')}
            accessibilityLabel={bs.appName}
            contentFit="contain"
            style={{ height, width: Math.round((height * 1900) / 396) }}
        />
    );
}
