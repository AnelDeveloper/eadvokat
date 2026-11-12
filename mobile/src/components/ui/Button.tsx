import { ActivityIndicator, Pressable, type PressableProps, StyleSheet, type ViewStyle } from 'react-native';
import { Text } from '@/components/ui/Text';
import { colors, radius } from '@/theme/tokens';

type Variant = 'primary' | 'outline' | 'subtle' | 'ghost' | 'confirm' | 'inactive';
type Size = 'sm' | 'md' | 'lg' | 'xl';

const variants: Record<Variant, { bg: string; fg: string; border?: string }> = {
    primary: { bg: colors.ink, fg: colors.white },
    outline: { bg: colors.white, fg: colors.ink, border: colors.ink },
    subtle: { bg: colors.white, fg: colors.ink, border: colors.lineStrong },
    ghost: { bg: colors.transparent, fg: colors.ink },
    confirm: { bg: colors.confirmed, fg: colors.white },
    // Looks disabled but stays pressable so it can explain what is missing.
    inactive: { bg: colors.faint, fg: colors.white },
};

const sizes: Record<Size, { height: number; radius: number; font: number }> = {
    sm: { height: 44, radius: radius.field, font: 13.5 },
    md: { height: 48, radius: radius.card, font: 14.5 },
    lg: { height: 52, radius: radius.card, font: 15.5 },
    xl: { height: 56, radius: radius.card, font: 15 },
};

export type ButtonProps = Omit<PressableProps, 'style' | 'children'> & {
    label: string;
    variant?: Variant;
    size?: Size;
    weight?: 500 | 600;
    busy?: boolean;
    style?: ViewStyle;
};

export function Button({ label, variant = 'primary', size = 'lg', weight = 600, busy = false, disabled, style, ...rest }: ButtonProps) {
    const v = variants[variant];
    const s = sizes[size];

    return (
        <Pressable
            accessibilityRole="button"
            disabled={disabled || busy}
            {...rest}
            style={({ pressed }) => [
                styles.base,
                {
                    minHeight: s.height,
                    borderRadius: s.radius,
                    backgroundColor: pressed && variant === 'primary' ? colors.inkDeep : v.bg,
                    borderWidth: v.border ? 1 : 0,
                    borderColor: v.border,
                    opacity: disabled ? 0.6 : 1,
                },
                style,
            ]}
        >
            {busy ? (
                <ActivityIndicator color={v.fg} />
            ) : (
                <Text size={s.font} weight={weight} color={v.fg} align="center">
                    {label}
                </Text>
            )}
        </Pressable>
    );
}

const styles = StyleSheet.create({
    base: { alignItems: 'center', justifyContent: 'center', paddingHorizontal: 12 },
});
