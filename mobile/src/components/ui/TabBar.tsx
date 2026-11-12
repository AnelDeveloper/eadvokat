import { router } from 'expo-router';
import type { BottomTabBarProps } from 'expo-router/js-tabs';
import { Pressable, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { bs } from '@shared/i18n/bs';
import { Text } from '@/components/ui/Text';
import { colors } from '@/theme/tokens';

const labels: Record<string, string> = {
    danas: bs.tabs.today,
    predmeti: bs.tabs.matters,
    kalendar: bs.tabs.calendar,
    posta: bs.tabs.mail,
};

/** Four equal text tabs; active: weight 600, ink, 3px ink top border. */
export function TabBar({ state, navigateToTab }: BottomTabBarProps) {
    const insets = useSafeAreaInsets();

    return (
        <View accessibilityRole="tabbar" style={[styles.bar, { paddingBottom: Math.max(10, insets.bottom) }]}>
            {state.routes.map((route, index) => {
                const active = state.index === index;

                return (
                    <Pressable
                        key={route.key}
                        accessibilityRole="tab"
                        accessibilityState={{ selected: active }}
                        onPress={() => {
                            // Tapping the active Predmeti tab returns to the list.
                            if (active && route.name === 'predmeti') {
                                router.navigate('/predmeti');
                            } else {
                                navigateToTab(route.key);
                            }
                        }}
                        style={[styles.tab, { borderTopColor: active ? colors.ink : colors.transparent }]}
                    >
                        <Text size={14} weight={active ? 600 : 500} color={active ? colors.ink : colors.muted}>
                            {labels[route.name] ?? route.name}
                        </Text>
                    </Pressable>
                );
            })}
        </View>
    );
}

const styles = StyleSheet.create({
    bar: { flexDirection: 'row', backgroundColor: colors.white, borderTopWidth: 1, borderTopColor: colors.lineStrong },
    tab: { flex: 1, minHeight: 58, alignItems: 'center', justifyContent: 'center', borderTopWidth: 3 },
});
