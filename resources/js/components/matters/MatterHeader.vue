<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { bs } from '@/i18n/bs';
import type { MatterHeader } from '@/types';

defineProps<{ matter: MatterHeader; active: 'timeline' | 'costs' | 'audit' }>();
</script>

<template>
    <div class="border-b border-line bg-white px-4.5 pt-0.5 pb-3.5 lg:px-8">
        <Link
            href="/predmeti"
            class="inline-flex h-11 items-center text-[15px] font-medium text-ink no-underline"
            >{{ bs.matters.back }}</Link
        >
        <div class="font-mono text-[17px] font-medium">
            {{ matter.caseNumber }}
        </div>
        <h1 class="mt-1 mb-0 text-matter leading-[1.25] font-semibold">
            {{ matter.title }}
        </h1>
        <div class="mt-[3px] text-[13px] text-muted">{{ matter.meta }}</div>
        <Link
            :href="`/predmeti/${matter.id}/troskovnik`"
            class="mt-3 flex min-h-14 w-full items-center justify-between gap-2.5 rounded-card bg-ink px-3.5 py-2 text-left text-white no-underline hover:text-white"
        >
            <span>
                <span class="block text-card font-semibold">{{
                    bs.matters.costButton
                }}</span>
                <span class="block text-sub text-line">{{
                    matter.costLine
                }}</span>
            </span>
            <span class="text-[20px]" aria-hidden="true">›</span>
        </Link>
    </div>
    <nav
        class="sticky top-0 z-[2] grid grid-cols-3 border-b border-line bg-white"
        :aria-label="bs.matters.title"
    >
        <Link
            v-for="tab in [
                { key: 'timeline', href: `/predmeti/${matter.id}` },
                { key: 'costs', href: `/predmeti/${matter.id}/troskovnik` },
                { key: 'audit', href: `/predmeti/${matter.id}/izmjene` },
            ] as const"
            :key="tab.key"
            :href="tab.href"
            preserve-scroll
            :aria-current="active === tab.key ? 'page' : undefined"
            class="flex h-[46px] items-center justify-center border-b-[3px] text-[14.5px] no-underline hover:text-ink"
            :class="
                active === tab.key
                    ? 'border-ink font-semibold text-ink'
                    : 'border-transparent font-medium text-muted'
            "
            >{{ bs.matters.tabs[tab.key] }}</Link
        >
    </nav>
</template>
