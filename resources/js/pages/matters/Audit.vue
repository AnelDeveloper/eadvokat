<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MatterHeader from '@/components/matters/MatterHeader.vue';
import { bs } from '@/i18n/bs';
import type { MatterHeader as Header } from '@/types';

defineProps<{
    matter: Header;
    entries: Array<{
        id: number;
        at: string;
        user: string;
        description: string;
    }>;
}>();
</script>

<template>
    <Head :title="`${bs.matters.tabs.audit} · ${matter.title}`" />
    <MatterHeader :matter="matter" active="audit" />

    <div class="px-4.5 pt-3.5 pb-[22px] lg:px-8">
        <div class="mb-1.5 text-[13px] text-muted">
            {{ bs.matters.auditIntro }}
        </div>
        <div
            v-for="entry in entries"
            :key="entry.id"
            class="border-t border-line py-2.5"
        >
            <div class="font-mono text-[12px] text-muted">
                {{ entry.at }} · {{ entry.user }}
            </div>
            <div class="mt-0.5 text-[14px] leading-[1.4]">
                {{ entry.description }}
            </div>
        </div>
    </div>
</template>
