<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import DateField from '@/components/ui/DateField.vue';
import ScreenBar from '@/components/ui/ScreenBar.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import SegmentedControl from '@/components/ui/SegmentedControl.vue';
import SourceQuote from '@/components/ui/SourceQuote.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import { showToast } from '@/composables/useToast';
import { bs } from '@/i18n/bs';
import {
    addDays,
    daysBetween,
    formatDate,
    formatDayMonth,
    weekday,
} from '@/lib/format';

const props = defineProps<{
    deadline: {
        id: number;
        matterId: number;
        title: string;
        ruleText: string;
        days: number | null;
        quote: string | null;
        highlight: string | null;
        sourceMeta: string;
        sourceDocumentId: number | null;
        jurisdiction: string;
        startDate: string;
        dueDate: string;
        remind3: boolean;
        remind1: boolean;
        isConfirmed: boolean;
    };
    jurisdictions: Array<{ value: string; label: string }>;
}>();

const t = bs.deadline;
const d = props.deadline;

// Acknowledgements always start unticked: each confirmation is a fresh, explicit act.
const form = useForm({
    jurisdiction: d.jurisdiction,
    start_date: d.startDate as string | null,
    due_date: d.dueDate as string | null,
    rule_ack: false,
    start_ack: false,
    due_ack: false,
    remind_3_days: d.remind3,
    remind_1_day: d.remind1,
});

// Rule 3: changing the start recomputes the last day and clears checks 2 and 3.
watch(
    () => form.start_date,
    (start) => {
        if (start && d.days !== null) {
            form.due_date = addDays(start, d.days);
        }

        form.start_ack = false;
        form.due_ack = false;
    },
);

// Editing the last day by hand clears check 3.
watch(
    () => form.due_date,
    () => (form.due_ack = false),
);

watch(
    () => form.jurisdiction,
    () => (form.rule_ack = false),
);

const checked = computed(
    () => [form.rule_ack, form.start_ack, form.due_ack].filter(Boolean).length,
);
const allChecked = computed(() => checked.value === 3);

const elapsed = computed(() =>
    form.start_date && form.due_date
        ? daysBetween(form.start_date, form.due_date)
        : null,
);
const isManual = computed(() => d.days !== null && elapsed.value !== d.days);
const dueInfo = computed(() => {
    if (!form.due_date || elapsed.value === null) {
        return bs.common.dateHint;
    }

    const day = weekday(form.due_date);

    return isManual.value || d.days === null
        ? t.dueManual(day, elapsed.value, d.days ?? elapsed.value)
        : t.dueComputed(day, elapsed.value);
});

const reminderOptions = computed(() => {
    const due = form.due_date ?? d.dueDate;

    return [
        {
            key: 'remind_3_days' as const,
            label: t.remind3(formatDayMonth(addDays(due, -3))),
        },
        {
            key: 'remind_1_day' as const,
            label: t.remind1(formatDayMonth(addDays(due, -1))),
        },
    ];
});

function confirm(): void {
    if (!allChecked.value) {
        showToast(t.needsChecksToast);

        return;
    }

    if (!form.due_date) {
        showToast(t.invalidDue);

        return;
    }

    form.post(`/rokovi/${d.id}/potvrda`, {
        onError: (errors) =>
            showToast(Object.values(errors)[0] ?? t.needsChecksToast),
    });
}

function later(): void {
    router.visit('/danas', {
        onSuccess: () => {
            if (!d.isConfirmed) {
                showToast(t.laterToast);
            }
        },
    });
}
</script>

<template>
    <Head :title="t.title" />
    <div class="flex min-h-svh flex-col">
        <ScreenBar :title="t.title" :step="t.step" @back="later" />

        <div class="flex-1 px-4.5 pt-3.5 pb-4.5">
            <div
                class="rounded-field border border-dashed px-3 py-2.5 text-note leading-[1.4]"
                :class="
                    d.isConfirmed
                        ? 'border-confirmed bg-confirmed-bg text-confirmed-text'
                        : 'border-proposed bg-proposed-bg text-proposed-text'
                "
            >
                <b>{{ d.isConfirmed ? t.confirmedBanner : t.banner }}</b>
                {{ d.isConfirmed ? t.confirmedBannerText : t.bannerText }}
            </div>

            <template v-if="d.quote">
                <SectionLabel class="mt-3.5">{{ t.source }}</SectionLabel>
                <SourceQuote
                    class="mt-1.5"
                    :text="d.quote"
                    :highlight="d.highlight"
                    :meta="d.sourceMeta"
                    :href="
                        d.sourceDocumentId
                            ? `/dokumenti/${d.sourceDocumentId}`
                            : undefined
                    "
                />
            </template>

            <div
                class="mt-3 rounded-card border border-line bg-white px-3.5 py-3 shadow-card"
            >
                <div class="text-field font-medium text-muted">
                    {{ t.rule }}
                </div>
                <div class="mt-0.5 text-[15px] font-medium">
                    {{ d.ruleText }}
                </div>
                <SegmentedControl
                    v-model="form.jurisdiction"
                    class="mt-2"
                    :options="jurisdictions"
                    text-size="text-[13px]"
                    :label="t.rule"
                />
                <Checkbox v-model="form.rule_ack" class="mt-2">{{
                    t.ruleAck
                }}</Checkbox>
            </div>

            <div
                class="mt-2 rounded-card border border-line bg-white px-3.5 py-3 shadow-card"
            >
                <DateField
                    v-model="form.start_date"
                    :label="t.start"
                    :error="form.errors.start_date"
                />
                <div class="mt-1 text-sub text-muted">{{ t.startHint }}</div>
                <Checkbox v-model="form.start_ack" class="mt-1">{{
                    t.startAck
                }}</Checkbox>
            </div>

            <div
                class="mt-2 rounded-card border border-line bg-white px-3.5 py-3 shadow-card"
            >
                <DateField
                    v-model="form.due_date"
                    :label="t.due"
                    :error="form.errors.due_date"
                />
                <div
                    class="mt-1 text-sub"
                    :class="
                        isManual || !form.due_date
                            ? 'text-proposed-text'
                            : 'text-muted'
                    "
                >
                    {{ dueInfo }}
                </div>
                <Checkbox v-model="form.due_ack" class="mt-1">{{
                    t.dueAck
                }}</Checkbox>
            </div>

            <SectionLabel class="mt-3">{{ t.assumptions }}</SectionLabel>
            <ul
                class="mt-1.5 mb-0 list-disc pl-[18px] text-note leading-[1.45] text-body"
            >
                <li v-for="a in t.assumptionList" :key="a">{{ a }}</li>
            </ul>

            <SectionLabel class="mt-3">{{ t.reminders }}</SectionLabel>
            <div class="mt-1.5 grid grid-cols-2 gap-1.5">
                <button
                    v-for="option in reminderOptions"
                    :key="option.key"
                    type="button"
                    role="switch"
                    :aria-checked="form[option.key]"
                    class="min-h-12 rounded-field border px-2 py-1 text-[13px] font-semibold"
                    :class="
                        form[option.key]
                            ? 'border-accent bg-accent text-white'
                            : 'border-line-strong bg-white text-ink'
                    "
                    @click="form[option.key] = !form[option.key]"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>

        <StickyActionBar>
            <Button
                block
                :variant="allChecked ? 'confirm' : 'inactive'"
                :disabled="form.processing"
                @click="confirm"
                >{{
                    allChecked
                        ? t.confirm(formatDate(form.due_date))
                        : t.needsChecks(checked)
                }}</Button
            >
            <Button
                block
                variant="ghost"
                size="sm"
                weight="medium"
                class="mt-1"
                @click="later"
                >{{ t.later }}</Button
            >
        </StickyActionBar>
    </div>
</template>
