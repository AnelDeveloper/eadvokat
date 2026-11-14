<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TabBar from '@/components/ui/TabBar.vue';
import type { TabItem } from '@/components/ui/TabBar.vue';
import Logo from '@/components/ui/Logo.vue';
import Toast from '@/components/ui/Toast.vue';
import { bs } from '@/i18n/bs';

const page = usePage();

const sections = [
    { key: 'today', label: bs.tabs.today, href: '/danas' },
    { key: 'matters', label: bs.tabs.matters, href: '/predmeti' },
    { key: 'calendar', label: bs.tabs.calendar, href: '/kalendar' },
    { key: 'mail', label: bs.tabs.mail, href: '/posta' },
] as const;

const user = computed(() => page.props.auth.user);
const settingsTab = computed<TabItem[]>(() => [
    {
        key: 'settings',
        label: bs.settings.open,
        href: '/postavke',
        active: page.url.startsWith('/postavke'),
    },
]);

const tabs = computed<TabItem[]>(() =>
    sections.map((s) => ({
        ...s,
        active:
            page.url === s.href ||
            page.url.startsWith(`${s.href}/`) ||
            page.url.startsWith(`${s.href}?`),
    })),
);
</script>

<template>
    <!-- Mobile-first: bottom tab bar; from 1024px the same tabs move to a left sidebar and the content sits in a centred column. -->
    <div
        class="min-h-svh bg-app [--rk-bar-offset:68px] lg:flex lg:[--rk-bar-offset:0px]"
    >
        <aside
            class="hidden lg:sticky lg:top-0 lg:flex lg:h-svh lg:w-[240px] lg:flex-none lg:flex-col lg:border-r lg:border-line lg:bg-white lg:pt-5"
        >
            <div class="px-4 pb-5">
                <Logo :height="28" />
            </div>
            <TabBar vertical :items="tabs" />
            <div v-if="user" class="mt-auto border-t border-line pt-3 pb-4">
                <TabBar vertical :items="settingsTab" />
                <div class="mt-2 px-5">
                    <span
                        class="block truncate text-[14px] font-semibold text-ink"
                        >{{ user.name }}</span
                    >
                    <span class="block truncate text-[13px] text-muted">{{
                        user.office
                    }}</span>
                    <span
                        v-if="user.trialDaysLeft !== null"
                        class="mt-2 inline-block rounded-full bg-accent-bg px-2.5 py-0.5 text-[12.5px] font-semibold text-accent-text"
                        >{{ bs.trial.daysLeft(user.trialDaysLeft) }}</span
                    >
                </div>
            </div>
        </aside>
        <main
            class="flex min-h-svh flex-col pb-17 lg:min-w-0 lg:flex-1 lg:pb-0"
        >
            <!-- Content column: full width on phones, centred and capped on wide screens so lines stay readable. -->
            <div
                class="flex w-full flex-1 flex-col lg:mx-auto lg:max-w-[1040px]"
            >
                <slot />
            </div>
        </main>
        <div class="fixed inset-x-0 bottom-0 z-20 lg:hidden">
            <TabBar :items="tabs" />
        </div>
        <Toast />
    </div>
</template>
