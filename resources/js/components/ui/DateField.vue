<script setup lang="ts">
import { ref, useId, watch } from 'vue';
import { formatDate, parseDate } from '@/lib/format';

/** ISO date (YYYY-MM-DD), or null while the typed text is not a valid date. */
const model = defineModel<string | null>({ required: true });

const {
    warn = false,
    large = true,
    mono = true,
} = defineProps<{
    label?: string;
    note?: string;
    error?: string;
    warn?: boolean;
    /** 16px mono as in the deadline proposal; false gives 15px. */
    large?: boolean;
    /** false renders the sans-serif field style of the mail review form. */
    mono?: boolean;
    name?: string;
}>();

const id = useId();
const text = ref(formatDate(model.value));

watch(model, (value) => {
    if (value !== parseDate(text.value)) {
        text.value = formatDate(value);
    }
});

function commit(): void {
    const iso = parseDate(text.value);
    model.value = iso;

    if (iso) {
        text.value = formatDate(iso);
    }
}
</script>

<template>
    <div>
        <label
            v-if="label"
            :for="id"
            class="text-field font-medium text-muted"
            >{{ label }}</label
        >
        <input
            :id="id"
            v-model="text"
            type="text"
            :name="name"
            inputmode="text"
            autocomplete="off"
            placeholder="dd.mm.gggg."
            class="mt-1 block h-11 w-full rounded-field border bg-white px-3 outline-none focus:border-ink"
            :class="[
                warn ? 'border-proposed' : 'border-line-strong',
                mono && 'font-mono',
                large ? 'text-[16px]' : 'text-[15px]',
            ]"
            @change="commit"
        />
        <span
            v-if="note"
            class="mt-[3px] block text-field text-proposed-text"
            >{{ note }}</span
        >
        <span v-if="error" class="mt-[3px] block text-field text-urgent-text">{{
            error
        }}</span>
    </div>
</template>
