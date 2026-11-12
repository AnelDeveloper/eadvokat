<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import DateField from '@/components/ui/DateField.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';
import { addDays } from '@/lib/format';

const props = defineProps<{
    matter: { id: number; caseNumber: string; title: string };
    entry: {
        id: number;
        number: string;
        docType: string;
        receivedOn: string;
        documentId: number | null;
    } | null;
    today: string;
}>();

const t = bs.newDeadline;
const form = useForm({
    title: '',
    rule_text: '',
    days: '' as string | number,
    start_date: (props.entry?.receivedOn ?? props.today) as string | null,
    due_date: null as string | null,
    source_quote: '',
    mail_entry_id: props.entry?.id ?? null,
});

const days = computed(() => {
    const n = Number(form.days);

    return Number.isInteger(n) && n > 0 ? n : null;
});

// Rule 3: last day = start + N calendar days (receipt day not counted); the lawyer can still change it.
watch([() => form.start_date, days], ([start, n]) => {
    if (start && n) {
        form.due_date = addDays(start, n);
    }
});
</script>

<template>
    <Head :title="t.title" />
    <div class="px-4.5 pt-1.5 pb-6 lg:px-8">
        <Link
            :href="`/predmeti/${matter.id}`"
            class="inline-flex h-11 items-center text-[15px] font-medium text-ink no-underline"
            >‹ {{ matter.caseNumber }}</Link
        >
        <h1 class="m-0 text-title font-semibold">{{ t.title }}</h1>
        <div class="mt-1 text-[14px] text-muted">{{ matter.title }}</div>
        <div v-if="entry" class="mt-1 font-mono text-sub text-muted">
            {{ t.fromEntry(entry.number, entry.docType) }}
        </div>

        <div
            class="mt-3 max-w-[720px] rounded-field border border-dashed border-proposed bg-proposed-bg px-3 py-2.5 text-note leading-[1.4] text-proposed-text"
        >
            {{ t.intro }}
        </div>

        <form
            class="mt-3 grid max-w-[720px] gap-3 rounded-card border border-line bg-white px-3.5 py-4"
            @submit.prevent="
                form
                    .transform((d) => ({ ...d, days: days }))
                    .post(`/predmeti/${matter.id}/rokovi`)
            "
        >
            <TextField
                v-model="form.title"
                :label="t.name"
                :placeholder="t.namePlaceholder"
                :error="form.errors.title"
            />
            <TextField
                v-model="form.rule_text"
                :label="t.rule"
                :placeholder="t.rulePlaceholder"
                :error="form.errors.rule_text"
            />
            <div class="grid gap-3 sm:grid-cols-3">
                <DateField
                    v-model="form.start_date"
                    :label="t.start"
                    :error="form.errors.start_date"
                />
                <TextField
                    v-model="form.days as string"
                    :label="t.days"
                    type="number"
                    mono
                    :error="form.errors.days"
                />
                <DateField
                    v-model="form.due_date"
                    :label="t.due"
                    :error="form.errors.due_date"
                />
            </div>
            <div class="text-sub text-muted">{{ t.computed }}</div>
            <label class="block">
                <span class="text-field font-medium text-muted">{{
                    t.quote
                }}</span>
                <textarea
                    v-model="form.source_quote"
                    rows="3"
                    class="mt-1 box-border block w-full resize-y rounded-field border border-line-strong bg-white p-2.5 font-serif text-[15px] leading-[1.4] outline-none focus:border-ink"
                />
            </label>
            <Button type="submit" block :disabled="form.processing">{{
                t.submit
            }}</Button>
            <Button
                :href="`/predmeti/${matter.id}`"
                block
                variant="ghost"
                size="sm"
                weight="medium"
                >{{ t.skip }}</Button
            >
        </form>
    </div>
</template>
