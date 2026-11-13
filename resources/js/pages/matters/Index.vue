<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/components/ui/Button.vue';
import { bs } from '@/i18n/bs';

defineProps<{
    matters: Array<{
        id: number;
        caseNumber: string;
        title: string;
        meta: string;
        next: string;
        costLine: string;
    }>;
    isDemo: boolean;
}>();
</script>

<template>
    <Head :title="bs.matters.title" />

    <div class="px-4.5 pt-1.5 pb-4.5 lg:px-8">
        <div class="mt-2 mb-3.5 flex items-center justify-between gap-3">
            <h1 class="m-0 text-title font-semibold">{{ bs.matters.title }}</h1>
            <Button href="/predmeti/novi" size="sm" class="px-4">{{
                bs.matters.create
            }}</Button>
        </div>
        <div
            v-if="matters.length === 0"
            class="rounded-card border border-dashed border-line-strong bg-white px-4 py-6 text-[15px] text-muted"
        >
            {{ bs.matters.emptyList }}
        </div>
        <div class="grid gap-2.5">
            <Link
                v-for="matter in matters"
                :key="matter.id"
                :href="`/predmeti/${matter.id}`"
                class="block overflow-hidden rounded-card border border-line bg-white text-left text-ink no-underline shadow-card hover:text-ink"
            >
                <span
                    class="block border-b border-line px-3.5 py-3 font-mono text-[15px] font-medium"
                    >{{ matter.caseNumber }}</span
                >
                <span class="block px-3.5 py-3">
                    <span
                        class="block text-[17px] leading-[1.3] font-semibold"
                        >{{ matter.title }}</span
                    >
                    <span class="mt-[3px] block text-[13px] text-muted">{{
                        matter.meta
                    }}</span>
                    <span
                        class="mt-2.5 block border-t border-hairline pt-2.5 text-note"
                        >{{ bs.matters.next }}
                        <b class="font-semibold">{{ matter.next }}</b></span
                    >
                    <span class="mt-[3px] block text-note"
                        >{{ bs.matters.costs }}
                        <b class="font-semibold">{{ matter.costLine }}</b></span
                    >
                </span>
            </Link>
        </div>
        <p v-if="isDemo" class="mx-0.5 mt-3.5 mb-0 text-[13px] text-muted">
            {{ bs.matters.demoNote }}
        </p>
    </div>
</template>
