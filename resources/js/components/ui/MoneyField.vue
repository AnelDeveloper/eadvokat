<script setup lang="ts">
import { ref, watch } from 'vue';
import { formatMoney, parseMoney } from '@/lib/format';

/** Decimal string ("480.00"), or null when empty or not a valid amount. */
const model = defineModel<string | null>({ required: true });

const { suffix = 'KM' } = defineProps<{ suffix?: string; label?: string }>();

const text = ref(formatMoney(model.value));

watch(model, (value) => {
    if (value !== parseMoney(text.value)) {
        text.value = formatMoney(value);
    }
});

function commit(): void {
    const value = parseMoney(text.value);
    model.value = value;

    if (value !== null) {
        text.value = formatMoney(value);
    }
}
</script>

<template>
    <label
        class="flex h-11 items-center gap-1.5 rounded-field border border-line-strong bg-white px-2.5 focus-within:border-ink"
    >
        <input
            v-model="text"
            inputmode="decimal"
            :aria-label="label"
            class="h-full w-full min-w-0 border-0 bg-transparent text-right font-mono text-[15px] outline-none"
            @change="commit"
        />
        <span class="text-field whitespace-nowrap text-muted">{{
            suffix
        }}</span>
    </label>
</template>
