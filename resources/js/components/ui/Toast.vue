<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { message } = useToast();
</script>

<template>
    <div
        role="status"
        aria-live="polite"
        class="pointer-events-none fixed inset-x-3.5 bottom-[calc(var(--rk-bar-offset,0px)+18px)] z-40 mx-auto max-w-[692px]"
    >
        <div
            v-if="message"
            class="rounded-card bg-ink px-3.5 py-3 text-[14px] leading-[1.35] text-white shadow-toast"
        >
            {{ message }}
        </div>
    </div>
</template>
