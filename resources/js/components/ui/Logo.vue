<script setup lang="ts">
import { bs } from '@/i18n/bs';

/** Ročište logo: calendar mark + wordmark. "light" is for dark backgrounds. */
const { tone = 'dark', height = 30 } = defineProps<{
    tone?: 'dark' | 'light';
    height?: number;
}>();
</script>

<template>
    <span
        class="inline-flex items-center font-serif leading-none font-semibold tracking-[-0.01em]"
        :class="tone === 'light' ? 'text-white' : 'text-ink'"
        :style="{ gap: `${height * 0.3}px`, fontSize: `${height * 0.86}px` }"
        role="img"
        :aria-label="bs.appName"
    >
        <svg
            viewBox="0 0 64 64"
            :width="height"
            :height="height"
            aria-hidden="true"
            class="flex-none"
        >
            <rect
                width="64"
                height="64"
                rx="15"
                :fill="tone === 'light' ? '#ffffff' : '#173247'"
            />
            <g
                :stroke="tone === 'light' ? '#173247' : '#ffffff'"
                fill="none"
                stroke-width="4"
            >
                <rect x="14" y="17" width="36" height="33" rx="6" />
                <path d="M14 27h36" />
                <path d="M24 12v9M40 12v9" stroke-linecap="round" />
            </g>
            <path
                d="M24.5 38.5l5.5 5.5l10.5-10.5"
                fill="none"
                :stroke="tone === 'light' ? '#2366d1' : '#6CB4FF'"
                stroke-width="4.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
        <span aria-hidden="true">{{ bs.appName }}</span>
    </span>
</template>
