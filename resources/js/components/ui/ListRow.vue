<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StatusChip from '@/components/ui/StatusChip.vue';
import type { DisplayStatus } from '@/types';

const {
    href,
    interactive = true,
    chevron = true,
    divider = 'top',
} = defineProps<{
    when: string;
    title: string;
    sub?: string;
    /** Uppercase type label above the title (Kalendar, Hronologija). */
    type?: string;
    status?: DisplayStatus;
    label?: string;
    href?: string;
    /** false renders a static row (landing hero phone). */
    interactive?: boolean;
    chevron?: boolean;
    divider?: 'top' | 'bottom';
}>();

defineEmits<{ click: [] }>();

const tag = computed(() => (!interactive ? 'div' : href ? Link : 'button'));
</script>

<template>
    <component
        :is="tag"
        :href="interactive ? href : undefined"
        :type="interactive && !href ? 'button' : undefined"
        class="grid min-h-14 w-full items-start gap-2.5 border-0 border-hairline bg-white px-3.5 py-2.5 text-left text-ink no-underline hover:text-ink"
        :class="[
            divider === 'top' ? 'border-t' : 'border-b',
            chevron
                ? 'grid-cols-[52px_minmax(0,1fr)_10px]'
                : 'grid-cols-[52px_minmax(0,1fr)]',
            interactive && 'active:bg-app',
        ]"
        @click="interactive && !href && $emit('click')"
    >
        <span class="pt-0.5 font-mono text-[13px] font-medium">{{ when }}</span>
        <span class="min-w-0">
            <span
                v-if="type"
                class="block font-semibold tracking-[.05em] text-chip text-muted uppercase"
                >{{ type }}</span
            >
            <span class="block text-row font-medium">{{ title }}</span>
            <span v-if="sub" class="mt-0.5 block text-sub text-muted">{{
                sub
            }}</span>
            <StatusChip
                v-if="status"
                :status="status"
                :label="label"
                class="mt-1.5"
            />
        </span>
        <span
            v-if="chevron"
            class="text-[18px] leading-[1.2] text-faint"
            aria-hidden="true"
            >›</span
        >
    </component>
</template>
