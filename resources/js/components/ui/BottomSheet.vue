<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue';

const open = defineModel<boolean>('open', { required: true });

const { contained = false } = defineProps<{
    title: string;
    /** Inside a phone frame: cover the frame, not the window. */
    contained?: boolean;
}>();

function onKey(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        open.value = false;
    }
}

watch(open, (value) => {
    if (typeof window === 'undefined') {
        return;
    }

    if (value) {
        window.addEventListener('keydown', onKey);
    } else {
        window.removeEventListener('keydown', onKey);
    }
});

onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <div
        v-if="open"
        class="inset-0 z-50 flex items-end bg-ink-deep/50"
        :class="contained ? 'absolute' : 'fixed'"
        @click.self="open = false"
    >
        <div
            role="dialog"
            aria-modal="true"
            :aria-label="title"
            class="mx-auto w-full max-w-[720px] rounded-t-sheet bg-white px-4.5 pt-5 pb-6.5"
        >
            <div class="text-[18px] font-semibold">{{ title }}</div>
            <slot />
        </div>
    </div>
</template>
