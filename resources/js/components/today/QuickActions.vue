<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import { bs } from '@/i18n/bs';

const { interactive = true } = defineProps<{
    captureHref?: string;
    dictateHref?: string;
    submissionHref?: string;
    interactive?: boolean;
}>();

const tag = interactive ? Link : 'span';
const base =
    'flex h-14 items-center justify-center rounded-card text-row font-semibold no-underline';
</script>

<template>
    <StickyActionBar>
        <SectionLabel class="mb-[7px]">{{ bs.today.quickInput }}</SectionLabel>
        <div class="grid grid-cols-[1.35fr_1fr_1fr] gap-2">
            <component
                :is="tag"
                :href="captureHref"
                :class="[base, 'gap-[9px] bg-ink text-white hover:text-white']"
            >
                <span
                    class="box-border grid h-3.5 w-5 place-items-center rounded-chip border-2 border-white"
                    aria-hidden="true"
                    ><span class="size-1.5 rounded-full bg-white"
                /></span>
                {{ bs.today.capture }}
            </component>
            <component
                :is="tag"
                :href="dictateHref"
                :class="[
                    base,
                    'border border-ink bg-white text-ink hover:text-ink',
                ]"
                >{{ bs.today.dictate }}</component
            >
            <component
                :is="tag"
                :href="submissionHref"
                :class="[
                    base,
                    'border border-ink bg-white text-ink hover:text-ink',
                ]"
                >{{ bs.today.submission }}</component
            >
        </div>
    </StickyActionBar>
</template>
