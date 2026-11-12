<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { bs } from '@/i18n/bs';

export type TabItem = {
    key: string;
    label: string;
    href: string;
    active: boolean;
};

const { vertical = false, interactive = true } = defineProps<{
    items: TabItem[];
    vertical?: boolean;
    interactive?: boolean;
}>();
</script>

<template>
    <nav
        :aria-label="bs.common.mainNav"
        :class="
            vertical
                ? 'flex flex-col gap-1'
                : 'grid h-17 flex-none grid-cols-4 border-t border-line-strong bg-white pb-2.5'
        "
    >
        <component
            :is="interactive ? Link : 'span'"
            v-for="item in items"
            :key="item.key"
            :href="interactive ? item.href : undefined"
            :aria-current="item.active ? 'page' : undefined"
            class="flex items-center no-underline hover:text-ink"
            :class="[
                vertical
                    ? 'min-h-12 border-l-[3px] px-4 text-[15px]'
                    : 'justify-center border-t-[3px] text-[14px]',
                item.active
                    ? 'border-ink font-semibold text-ink'
                    : 'border-transparent font-medium text-muted',
            ]"
            >{{ item.label }}</component
        >
    </nav>
</template>
