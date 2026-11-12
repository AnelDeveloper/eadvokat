import { Text as RNText, type TextProps, type TextStyle } from 'react-native';
import { colors, fonts } from '@/theme/tokens';

type Family = 'sans' | 'mono' | 'serif';
type Weight = 400 | 500 | 600;

export type AppTextProps = TextProps & {
    family?: Family;
    weight?: Weight;
    italic?: boolean;
    size?: number;
    lineHeight?: number;
    color?: string;
    align?: TextStyle['textAlign'];
    uppercase?: boolean;
    tracking?: number;
};

function fontFamily(family: Family, weight: Weight, italic: boolean): string {
    if (family === 'mono') {
        return weight >= 500 ? fonts.mono500 : fonts.mono400;
    }

    if (family === 'serif') {
        if (italic) {
            return fonts.serif400Italic;
        }

        return weight === 600 ? fonts.serif600 : weight === 500 ? fonts.serif500 : fonts.serif400;
    }

    return weight === 600 ? fonts.sans600 : weight === 500 ? fonts.sans500 : fonts.sans400;
}

/** All text in the app goes through here so fonts and weights stay consistent. */
export function Text({
    family = 'sans',
    weight = 400,
    italic = false,
    size = 15,
    lineHeight,
    color = colors.ink,
    align,
    uppercase,
    tracking,
    style,
    ...rest
}: AppTextProps) {
    return (
        <RNText
            {...rest}
            style={[
                {
                    fontFamily: fontFamily(family, weight, italic),
                    fontSize: size,
                    lineHeight: lineHeight ? size * lineHeight : undefined,
                    color,
                    textAlign: align,
                    textTransform: uppercase ? 'uppercase' : undefined,
                    letterSpacing: tracking ? tracking * size : undefined,
                },
                style,
            ]}
        />
    );
}
