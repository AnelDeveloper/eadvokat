import { IBMPlexMono_400Regular, IBMPlexMono_500Medium } from '@expo-google-fonts/ibm-plex-mono';
import { IBMPlexSans_400Regular, IBMPlexSans_500Medium, IBMPlexSans_600SemiBold } from '@expo-google-fonts/ibm-plex-sans';
import {
    Newsreader_400Regular,
    Newsreader_400Regular_Italic,
    Newsreader_500Medium,
    Newsreader_600SemiBold,
} from '@expo-google-fonts/newsreader';
import { useFonts } from 'expo-font';
import { Stack } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { StatusBar } from 'expo-status-bar';
import { useEffect } from 'react';
import { AuthProvider, useAuth } from '@/lib/auth';
import { ToastProvider } from '@/lib/toast';
import { colors } from '@/theme/tokens';

void SplashScreen.preventAutoHideAsync();

function Navigation() {
    const { ready, user } = useAuth();
    const [fontsLoaded] = useFonts({
        IBMPlexSans_400Regular,
        IBMPlexSans_500Medium,
        IBMPlexSans_600SemiBold,
        IBMPlexMono_400Regular,
        IBMPlexMono_500Medium,
        Newsreader_400Regular,
        Newsreader_400Regular_Italic,
        Newsreader_500Medium,
        Newsreader_600SemiBold,
    });

    useEffect(() => {
        if (ready && fontsLoaded) {
            void SplashScreen.hideAsync();
        }
    }, [ready, fontsLoaded]);

    if (!ready || !fontsLoaded) {
        return null;
    }

    return (
        <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: colors.app } }}>
            <Stack.Protected guard={!user}>
                <Stack.Screen name="login" />
                <Stack.Screen name="registracija" />
            </Stack.Protected>
            <Stack.Protected guard={!!user}>
                <Stack.Screen name="(tabs)" />
                <Stack.Screen name="posta/fotografisi" options={{ contentStyle: { backgroundColor: colors.inkDeep } }} />
                <Stack.Screen name="posta/pregled/[id]" />
                <Stack.Screen name="posta/podnesak" />
                <Stack.Screen name="rokovi/[id]/prijedlog" />
                <Stack.Screen name="diktat/index" />
                <Stack.Screen name="diktat/[id]" />
                <Stack.Screen name="dokumenti/[id]" options={{ contentStyle: { backgroundColor: colors.viewer } }} />
                <Stack.Screen name="postavke" />
            </Stack.Protected>
        </Stack>
    );
}

export default function RootLayout() {
    return (
        <AuthProvider>
            <ToastProvider>
                <StatusBar style="dark" />
                <Navigation />
            </ToastProvider>
        </AuthProvider>
    );
}
