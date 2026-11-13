<script setup lang="ts">
import { useId } from 'vue';

const model = defineModel<string>({ required: true });

const {
    warn = false,
    mono = false,
    type = 'text',
} = defineProps<{
    label?: string;
    note?: string;
    error?: string;
    warn?: boolean;
    mono?: boolean;
    type?: string;
    name?: string;
    placeholder?: string;
    autocomplete?: string;
    /** id of a <datalist> with suggestions */
    list?: string;
}>();

const id = useId();
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
            v-model="model"
            :type="type"
            :name="name"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :list="list"
            class="mt-1 block h-11 w-full rounded-field border bg-white px-3 outline-none focus:border-accent focus:ring-3 focus:ring-accent/15"
            :class="[
                warn ? 'border-proposed' : 'border-line-strong',
                mono ? 'font-mono text-[15px]' : 'text-[15px]',
            ]"
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
