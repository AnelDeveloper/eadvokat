<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import { bs } from '@/i18n/bs';

const { interactive = true } = defineProps<{
    captureHref?: string;
    dictateHref?: string;
    submissionHref?: string;
    /** false: static mock-up (landing page phone), always with the phone wording. */
    interactive?: boolean;
}>();

const tag = interactive ? Link : 'span';
const base =
    'flex min-h-14 items-center justify-center gap-2 rounded-card px-1.5 py-1.5 text-center text-row leading-tight font-semibold no-underline max-sm:flex-col max-sm:gap-0.5 max-sm:text-[14px]';
</script>

<template>
    <StickyActionBar>
        <SectionLabel class="mb-[7px]">{{ bs.today.quickInput }}</SectionLabel>
        <div class="grid grid-cols-[1.35fr_1fr_1fr] gap-2">
            <component
                :is="tag"
                :href="captureHref"
                :class="[
                    base,
                    'bg-accent text-white shadow-[0_1px_2px_rgb(16_32_48/0.12)] hover:bg-accent-hover hover:text-white',
                ]"
            >
                <!-- Camera on touch devices; on a computer the document is uploaded. -->
                <template v-if="interactive">
                    <Icon name="camera" class="hidden pointer-coarse:block" />
                    <Icon name="upload" class="pointer-coarse:hidden" />
                    <span class="hidden pointer-coarse:inline">{{
                        bs.today.capture
                    }}</span>
                    <span class="pointer-coarse:hidden">{{
                        bs.today.upload
                    }}</span>
                </template>
                <template v-else
                    ><Icon name="camera" />{{ bs.today.capture }}</template
                >
            </component>
            <component
                :is="tag"
                :href="dictateHref"
                :class="[
                    base,
                    'border border-line-strong bg-white text-ink hover:border-accent hover:text-accent-text',
                ]"
                ><Icon name="mic" class="text-nav-calendar" />{{
                    bs.today.dictate
                }}</component
            >
            <component
                :is="tag"
                :href="submissionHref"
                :class="[
                    base,
                    'border border-line-strong bg-white text-ink hover:border-accent hover:text-accent-text',
                ]"
                ><Icon name="send" class="text-nav-mail" />{{
                    bs.today.submission
                }}</component
            >
        </div>
    </StickyActionBar>
</template>
