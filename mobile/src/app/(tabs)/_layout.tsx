import { Tabs } from 'expo-router/js-tabs';
import { TabBar } from '@/components/ui/TabBar';
import { colors } from '@/theme/tokens';

export default function TabsLayout() {
    return (
        <Tabs tabBar={(props) => <TabBar {...props} />} screenOptions={{ headerShown: false, sceneStyle: { backgroundColor: colors.app } }}>
            <Tabs.Screen name="danas" />
            <Tabs.Screen name="predmeti" />
            <Tabs.Screen name="kalendar" />
            <Tabs.Screen name="posta" />
        </Tabs>
    );
}
