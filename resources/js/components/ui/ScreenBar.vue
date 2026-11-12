<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { bs } from '@/i18n/bs';

/** White top bar of full-screen flows: back · title · step. */
defineProps<{ title: string; backUrl?: string; step?: string }>();
defineEmits<{ back: [] }>();
</script>

<template>
    <div
        class="sticky top-0 z-10 grid flex-none grid-cols-[44px_1fr_44px] items-center border-b border-line bg-white px-2"
    >
        <Link
            v-if="backUrl"
            :href="backUrl"
            :aria-label="bs.common.back"
            class="flex h-12 w-11 items-center justify-center text-[24px] text-ink no-underline"
            >‹</Link
        >
        <button
            v-else
            type="button"
            :aria-label="bs.common.back"
            class="h-12 w-11 border-0 bg-transparent text-[24px]"
            @click="$emit('back')"
        >
            ‹
        </button>
        <h1 class="m-0 text-center text-card font-semibold">{{ title }}</h1>
        <span class="text-center font-mono text-[12px] text-muted">{{
            step
        }}</span>
    </div>
</template>
