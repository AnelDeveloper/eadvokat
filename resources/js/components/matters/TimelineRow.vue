<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import StatusChip from '@/components/ui/StatusChip.vue';
import { bs } from '@/i18n/bs';
import type { AgendaRow, DisplayStatus } from '@/types';

defineProps<{ row: AgendaRow }>();

// Dot on the rail, coloured by status; a proposal is a white dot with a dashed amber ring.
const dot: Record<DisplayStatus, string> = {
    proposed: 'border border-dashed border-proposed bg-white',
    confirmed: 'border border-confirmed bg-confirmed',
    urgent: 'border border-urgent bg-urgent',
    recorded: 'border border-line-strong bg-faint',
    fulfilled: 'border border-line-strong bg-faint',
};
</script>

<template>
    <div class="relative grid grid-cols-[88px_12px_minmax(0,1fr)] gap-2">
        <span class="pt-px font-mono text-[12px] whitespace-nowrap text-ink"
            >{{ row.when
            }}<span v-if="row.time" class="block text-muted">{{
                row.time
            }}</span></span
        >
        <span class="relative" aria-hidden="true">
            <span
                class="absolute top-0 bottom-0 left-[5px] w-px bg-line-strong"
            />
            <span
                class="absolute top-1 left-px box-border size-[9px] rounded-full"
                :class="dot[row.status]"
            />
        </span>
        <span class="min-w-0 pb-4">
            <span
                class="block text-chip font-semibold tracking-[.05em] text-muted uppercase"
                >{{ row.type }}</span
            >
            <component
                :is="row.href ? Link : 'span'"
                :href="row.href ?? undefined"
                class="block text-row font-medium text-ink no-underline hover:text-ink"
                :class="row.href && 'after:absolute after:inset-0'"
                >{{ row.title }}</component
            >
            <span class="mt-0.5 block text-sub text-muted">{{ row.sub }}</span>
            <StatusChip :status="row.status" class="mt-1.5" />
            <Link
                v-if="row.documentId"
                :href="`/dokumenti/${row.documentId}`"
                class="relative z-[1] mt-0.5 flex min-h-11 items-center text-[13px] font-semibold text-ink underline"
                >{{ bs.common.sourceDocument }}</Link
            >
        </span>
    </div>
</template>
