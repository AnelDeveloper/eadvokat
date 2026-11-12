<script setup lang="ts">
const { tone = 'app' } = defineProps<{
    clock: string;
    tone?: 'app' | 'surface' | 'dark';
}>();

const tones = {
    app: 'bg-app text-ink',
    surface: 'bg-white text-ink',
    dark: 'bg-ink-deep text-white',
} as const;
</script>

<template>
    <div
        class="grid h-[50px] flex-none grid-cols-[1fr_auto_1fr] items-center px-6.5"
        :class="tones[tone]"
    >
        <span class="text-[15px] font-semibold">{{ clock }}</span>
        <span class="flex items-center"><slot /></span>
        <span class="flex items-center justify-self-end" aria-hidden="true">
            <span
                class="relative h-2.5 w-[17px] rounded-[3px] border-[1.5px] border-current"
            >
                <span
                    class="absolute inset-[1.5px] right-1 rounded-[1px] bg-current"
                />
            </span>
        </span>
    </div>
</template>
