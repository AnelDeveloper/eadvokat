<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { bs } from '@/i18n/bs';

const props = withDefaults(
    defineProps<{
        text: string;
        /** Exact passage inside `text` to mark as the source of a proposal. */
        highlight?: string | null;
        variant?: 'source' | 'excerpt';
        /** Note under an excerpt; the app quotes and never interprets. */
        note?: string;
        meta?: string;
        href?: string;
    }>(),
    { variant: 'source', highlight: null },
);

const parts = computed(() => {
    const at = props.highlight ? props.text.indexOf(props.highlight) : -1;

    if (!props.highlight || at < 0) {
        return { before: props.text, mark: '', after: '' };
    }

    return {
        before: props.text.slice(0, at),
        mark: props.highlight,
        after: props.text.slice(at + props.highlight.length),
    };
});
</script>

<template>
    <figure
        class="m-0 bg-white"
        :class="
            variant === 'source'
                ? 'rounded-chip border border-line-strong px-3.5 py-3'
                : 'rounded-field border border-line px-3 py-2.5'
        "
    >
        <blockquote
            class="m-0 font-serif leading-[1.4]"
            :class="variant === 'source' ? 'text-[16px]' : 'text-[15px] italic'"
        >
            „{{ parts.before
            }}<mark
                v-if="parts.mark"
                class="bg-proposed-bg text-ink shadow-highlight"
                >{{ parts.mark }}</mark
            >{{ parts.after }}“
        </blockquote>
        <figcaption v-if="note" class="mt-[5px] text-sub text-proposed-text">
            {{ note }}
        </figcaption>
        <div
            v-if="meta || href"
            class="mt-1.5 flex items-center justify-between gap-2"
        >
            <span class="font-mono text-[12px] text-muted">{{ meta }}</span>
            <Link
                v-if="href"
                :href="href"
                class="flex min-h-11 items-center text-[13px] font-semibold text-ink underline"
                >{{ bs.common.open }}</Link
            >
        </div>
    </figure>
</template>
