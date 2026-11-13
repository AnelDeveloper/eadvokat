<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';
import type { IconName } from '@/components/ui/Icon.vue';
import { bs } from '@/i18n/bs';

export type TabItem = {
    key: 'today' | 'matters' | 'calendar' | 'mail' | 'settings';
    label: string;
    href: string;
    active: boolean;
};

const { vertical = false, interactive = true } = defineProps<{
    items: TabItem[];
    vertical?: boolean;
    interactive?: boolean;
}>();

/** Each section keeps its own colour everywhere (sidebar, tab bar). */
const tone: Record<
    TabItem['key'],
    { icon: IconName; text: string; bg: string; solid: string }
> = {
    today: {
        icon: 'today',
        text: 'text-nav-today',
        bg: 'bg-nav-today-bg',
        solid: 'bg-nav-today',
    },
    matters: {
        icon: 'matters',
        text: 'text-nav-matters',
        bg: 'bg-nav-matters-bg',
        solid: 'bg-nav-matters',
    },
    calendar: {
        icon: 'calendar',
        text: 'text-nav-calendar',
        bg: 'bg-nav-calendar-bg',
        solid: 'bg-nav-calendar',
    },
    mail: {
        icon: 'mail',
        text: 'text-nav-mail',
        bg: 'bg-nav-mail-bg',
        solid: 'bg-nav-mail',
    },
    settings: {
        icon: 'settings',
        text: 'text-nav-settings',
        bg: 'bg-nav-settings-bg',
        solid: 'bg-nav-settings',
    },
};
</script>

<template>
    <nav
        :aria-label="bs.common.mainNav"
        :class="
            vertical
                ? 'flex flex-col gap-1 px-3'
                : 'grid h-17 flex-none grid-cols-4 border-t border-line-strong bg-white pt-1.5 pb-2'
        "
    >
        <component
            :is="interactive ? Link : 'span'"
            v-for="item in items"
            :key="item.key"
            :href="interactive ? item.href : undefined"
            :prefetch="interactive ? 'hover' : undefined"
            :aria-current="item.active ? 'page' : undefined"
            class="flex items-center no-underline hover:text-ink"
            :class="[
                vertical
                    ? 'min-h-11 gap-3 rounded-field px-2 text-[15px] transition-colors hover:bg-app'
                    : 'flex-col justify-center gap-0.5 text-[12.5px]',
                item.active
                    ? 'font-semibold text-ink'
                    : 'font-medium text-muted',
                vertical && item.active && 'bg-app',
            ]"
        >
            <span
                class="grid place-items-center transition-colors"
                :class="[
                    vertical ? 'size-8 rounded-[9px]' : 'h-7 w-12 rounded-full',
                    item.active
                        ? [tone[item.key].solid, 'text-white']
                        : [tone[item.key].bg, tone[item.key].text],
                ]"
            >
                <Icon
                    :name="tone[item.key].icon"
                    :size="vertical ? 18 : 19"
                    :stroke="item.active ? 2.1 : 1.8"
                />
            </span>
            {{ item.label }}
        </component>
    </nav>
</template>
