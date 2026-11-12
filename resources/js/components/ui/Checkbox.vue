<script setup lang="ts">
const model = defineModel<boolean>({ required: true });
</script>

<template>
    <button
        type="button"
        role="checkbox"
        :aria-checked="model"
        class="flex min-h-11 w-full items-center gap-2.5 border-0 bg-transparent p-0 text-left text-[14.5px] font-medium"
        @click="model = !model"
    >
        <span
            class="grid size-6 flex-none place-items-center rounded-checkbox border-2 text-[15px] text-white"
            :class="
                model
                    ? 'border-confirmed bg-confirmed'
                    : 'border-faint bg-white'
            "
            aria-hidden="true"
            >{{ model ? '✓' : '' }}</span
        >
        <span><slot /></span>
    </button>
</template>
