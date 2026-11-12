<script setup lang="ts">
import { bs } from '@/i18n/bs';

/** The eAdvokat logo from the brand kit; "light" is the version for dark backgrounds. */
const { tone = 'dark', height = 30 } = defineProps<{
    tone?: 'dark' | 'light';
    height?: number;
}>();

// Cropped artwork is 1900 × 396.
const ratio = 1900 / 396;
</script>

<template>
    <img
        :src="tone === 'light' ? '/brand/logo-light.png' : '/brand/logo.png'"
        :alt="bs.appName"
        :width="Math.round(height * ratio)"
        :height="height"
        class="block h-auto max-w-full"
        :style="{ height: `${height}px`, width: 'auto' }"
        decoding="async"
    />
</template>
