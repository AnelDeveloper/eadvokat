<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TabBar from '@/components/ui/TabBar.vue';
import type { TabItem } from '@/components/ui/TabBar.vue';
import Toast from '@/components/ui/Toast.vue';
import { bs } from '@/i18n/bs';

const page = usePage();

const sections = [
    { key: 'today', label: bs.tabs.today, href: '/danas' },
    { key: 'matters', label: bs.tabs.matters, href: '/predmeti' },
    { key: 'calendar', label: bs.tabs.calendar, href: '/kalendar' },
    { key: 'mail', label: bs.tabs.mail, href: '/posta' },
] as const;

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
    <!-- Mobile-first: bottom tab bar; from 1024px the same tabs move to a left sidebar. -->
    <div
        class="min-h-svh bg-app [--rk-bar-offset:68px] lg:flex lg:justify-center lg:[--rk-bar-offset:0px]"
    >
        <aside
            class="hidden lg:sticky lg:top-0 lg:block lg:h-svh lg:w-[220px] lg:flex-none lg:border-r lg:border-line lg:bg-white lg:pt-5"
        >
            <div
                class="px-4 pb-5 font-serif text-[24px] font-semibold tracking-[-.01em]"
            >
                {{ bs.appName }}
            </div>
            <TabBar vertical :items="tabs" />
        </aside>
        <main
            class="flex min-h-svh flex-col pb-17 lg:w-full lg:max-w-[720px] lg:pb-0"
        >
            <slot />
        </main>
        <div class="fixed inset-x-0 bottom-0 z-20 lg:hidden">
            <TabBar :items="tabs" />
        </div>
        <Toast />
    </div>
</template>
