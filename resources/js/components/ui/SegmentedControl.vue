<script setup lang="ts" generic="T extends string | number">
const model = defineModel<T>({ required: true });

const {
    columns,
    tall = false,
    textSize = 'text-[13.5px]',
} = defineProps<{
    options: ReadonlyArray<{ value: T; label: string }>;
    columns?: number;
    tall?: boolean;
    textSize?: string;
    label?: string;
}>();
</script>

<template>
    <div
        role="radiogroup"
        :aria-label="label"
        class="grid gap-1.5"
        :style="{
            gridTemplateColumns: `repeat(${columns ?? options.length}, minmax(0, 1fr))`,
        }"
    >
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            role="radio"
            :aria-checked="model === option.value"
            class="rounded-field border px-2 leading-tight font-semibold transition-colors"
            :class="[
                tall ? 'min-h-12 py-1' : 'min-h-11 py-1',
                textSize,
                model === option.value
                    ? 'border-accent bg-accent text-white'
                    : 'border-line-strong bg-white text-ink',
            ]"
            @click="model = option.value"
        >
            {{ option.label }}
        </button>
    </div>
</template>
