<script setup lang="ts">
const { band = false, as = 'div' } = defineProps<{
    band?: boolean;
    as?: string;
}>();
</script>

<template>
    <component
        :is="as"
        class="text-label font-semibold tracking-label text-muted uppercase"
        :class="band && 'border-t border-line bg-app px-3.5 pt-[9px] pb-[5px]'"
    >
        <slot />
    </component>
</template>
