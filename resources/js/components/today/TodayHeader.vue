<script setup lang="ts">
import { bs } from '@/i18n/bs';

const { as = 'h1' } = defineProps<{ date: string; as?: string }>();
</script>

<template>
    <div class="flex items-end justify-between gap-3">
        <div>
            <div class="font-mono text-[12px] text-muted">{{ date }}</div>
            <component :is="as" class="mt-0.5 mb-0 text-title font-semibold">{{
                bs.today.title
            }}</component>
        </div>
        <slot />
    </div>
</template>
