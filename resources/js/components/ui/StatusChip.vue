<script setup lang="ts">
import { bs } from '@/i18n/bs';
import type { DisplayStatus } from '@/types';

const { size = 'sm', label } = defineProps<{
    status: DisplayStatus;
    label?: string;
    size?: 'sm' | 'md';
}>();

// Dashed amber always means "not active yet"; a proposal is never solid or green.
const tone: Record<DisplayStatus, string> = {
    proposed: 'border-dashed border-proposed bg-proposed-bg text-proposed-text',
    confirmed: 'border-confirmed bg-confirmed-bg text-confirmed-text',
    urgent: 'border-urgent bg-urgent-bg text-urgent-text',
    recorded: 'border-line-strong bg-neutral-bg text-muted',
    fulfilled: 'border-line-strong bg-neutral-bg text-muted',
};
</script>

<template>
    <span
        class="rounded-chip border font-semibold"
        :class="[
            tone[status],
            size === 'sm'
                ? 'inline-block px-[7px] py-1 text-chip'
                : 'inline px-2 py-1 text-[12px]',
        ]"
        >{{ label ?? bs.status[status] }}</span
    >
</template>
