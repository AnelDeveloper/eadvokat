<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import DateField from '@/components/ui/DateField.vue';
import ScreenBar from '@/components/ui/ScreenBar.vue';
import SegmentedControl from '@/components/ui/SegmentedControl.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';
import {
    WEEKDAYS_SHORT,
    addDays,
    formatDate,
    formatDayMonth,
    fromIso,
} from '@/lib/format';

type MatterOption = {
    id: number;
    caseNumber: string;
    title: string;
    openDeadlines: Array<{ id: number; due: string; title: string }>;
};

const props = defineProps<{
    matters: MatterOption[];
    nextNumber: string;
    today: string;
    defaults: { docType: string; reminderText: string };
    deliveryMethods: Array<{ value: string; label: string }>;
}>();

const t = bs.submission;
/** Follow-up reminder offset in days (from the prototype). */
const FOLLOW_UP_DAYS = 10;

const form = useForm({
    matter_id: props.matters[0]?.id ?? 0,
    doc_type: props.defaults.docType,
    sent_on: props.today as string | null,
    delivery_method: 'in_person',
    proof: null as File | null,
    fulfils: [] as number[],
    reminder_text: props.defaults.reminderText,
    reminder_on: '',
});

const matter = computed(() =>
    props.matters.find((m) => m.id === form.matter_id),
);
const proofInput = ref<HTMLInputElement | null>(null);

// When the computed day falls on a weekend, the lawyer picks it or the next Monday.
const followUp = computed(() => {
    const base = form.sent_on ?? props.today;
    const day = addDays(base, FOLLOW_UP_DAYS);
    const dow = fromIso(day).getDay();
    const weekend = dow === 6 || dow === 0;

    return {
        base,
        day,
        weekend,
        weekdayAccusative: dow === 6 ? 'subotu' : 'nedjelju',
        options: weekend ? [day, addDays(day, dow === 6 ? 2 : 1)] : [day],
    };
});

watch(
    followUp,
    (f) => {
        if (!f.options.includes(form.reminder_on)) {
            form.reminder_on = f.options[f.options.length - 1];
        }
    },
    { immediate: true },
);

watch(
    () => form.matter_id,
    () => (form.fulfils = []),
);

const optionLabel = (iso: string) =>
    `${WEEKDAYS_SHORT[fromIso(iso).getDay()]}, ${formatDayMonth(iso)}`;

function onProof(event: Event): void {
    form.proof = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function toggleFulfil(id: number, on: boolean): void {
    form.fulfils = on
        ? [...form.fulfils, id]
        : form.fulfils.filter((x) => x !== id);
}

function save(): void {
    form.post('/posta/podnesak', { forceFormData: true });
}
</script>

<template>
    <Head :title="t.title" />
    <div class="flex min-h-svh flex-col">
        <ScreenBar :title="t.title" back-url="/danas" />

        <form
            id="submission"
            class="flex-1 px-4.5 pt-3.5 pb-4.5"
            @submit.prevent="save"
        >
            <div class="font-mono text-sub text-muted">
                {{ nextNumber }} · {{ matter?.caseNumber }}
            </div>

            <label v-if="matters.length > 1" class="mt-2.5 block">
                <span class="text-field font-medium text-muted">{{
                    t.matter
                }}</span>
                <select
                    v-model="form.matter_id"
                    class="mt-1 block h-11 w-full rounded-field border border-line-strong bg-white px-3 text-[15px]"
                >
                    <option v-for="m in matters" :key="m.id" :value="m.id">
                        {{ m.caseNumber }} · {{ m.title }}
                    </option>
                </select>
            </label>

            <TextField
                v-model="form.doc_type"
                class="mt-2.5"
                :label="t.docType"
                :placeholder="t.docTypePlaceholder"
                :error="form.errors.doc_type"
            />
            <DateField
                v-model="form.sent_on"
                class="mt-2.5"
                :label="t.sentOn"
                :large="false"
                :error="form.errors.sent_on"
            />

            <div class="mt-2.5 text-field font-medium text-muted">
                {{ t.method }}
            </div>
            <SegmentedControl
                v-model="form.delivery_method"
                class="mt-1"
                :options="deliveryMethods"
                :label="t.method"
            />

            <div class="mt-3.5 text-field font-medium text-muted">
                {{ t.proof }}
            </div>
            <input
                ref="proofInput"
                type="file"
                accept="image/*,application/pdf"
                capture="environment"
                class="sr-only"
                tabindex="-1"
                @change="onProof"
            />
            <button
                type="button"
                class="mt-1 flex min-h-16 w-full items-center gap-3 rounded-field px-3 py-2.5 text-left"
                :class="
                    form.proof
                        ? 'border border-confirmed bg-confirmed-bg'
                        : 'border border-dashed border-faint bg-white'
                "
                @click="proofInput?.click()"
            >
                <span
                    class="grid h-12 w-10 flex-none place-items-center rounded-[3px] border border-line-strong bg-white text-center font-mono text-[9px] leading-[1.1]"
                    :class="form.proof ? 'text-confirmed-text' : 'text-ink'"
                    aria-hidden="true"
                    >{{
                        form.proof
                            ? t.proofThumb(
                                  formatDayMonth(form.sent_on ?? today),
                              )
                            : '+'
                    }}</span
                >
                <span>
                    <span
                        class="block text-[14.5px] font-semibold"
                        :class="form.proof ? 'text-confirmed-text' : 'text-ink'"
                        ><template v-if="form.proof">{{
                            t.proofAttached
                        }}</template
                        ><template v-else
                            ><span class="hidden pointer-coarse:inline">{{
                                t.proofEmpty
                            }}</span
                            ><span class="pointer-coarse:hidden">{{
                                t.proofUpload
                            }}</span></template
                        ></span
                    >
                    <span class="block text-sub text-muted"
                        ><template v-if="form.proof">{{
                            form.proof.name
                        }}</template
                        ><template v-else
                            ><span class="hidden pointer-coarse:inline">{{
                                t.proofEmptySub
                            }}</span
                            ><span class="pointer-coarse:hidden">{{
                                t.proofUploadSub
                            }}</span></template
                        ></span
                    >
                </span>
            </button>
            <div
                v-if="form.errors.proof"
                class="mt-1 text-field text-urgent-text"
            >
                {{ form.errors.proof }}
            </div>

            <Checkbox
                v-for="d in matter?.openDeadlines ?? []"
                :key="d.id"
                class="mt-2.5"
                :model-value="form.fulfils.includes(d.id)"
                @update:model-value="(on: boolean) => toggleFulfil(d.id, on)"
                >{{ t.fulfils(d.due) }}</Checkbox
            >

            <div class="mt-3.5 border-t-2 border-ink pt-3.5">
                <div class="text-card font-semibold">{{ t.reminder }}</div>
                <TextField
                    v-model="form.reminder_text"
                    class="-mt-1"
                    :error="form.errors.reminder_text"
                />
                <template v-if="followUp.weekend">
                    <div class="mt-1.5 text-sub text-proposed-text">
                        {{
                            t.weekendNote(
                                formatDate(followUp.base),
                                followUp.weekdayAccusative,
                            )
                        }}
                    </div>
                    <SegmentedControl
                        v-model="form.reminder_on"
                        class="mt-[5px]"
                        :options="
                            followUp.options.map((d) => ({
                                value: d,
                                label: optionLabel(d),
                            }))
                        "
                        :label="t.reminder"
                    />
                </template>
                <div v-else class="mt-1.5 text-sub text-muted">
                    {{ t.reminderOn(optionLabel(form.reminder_on)) }}
                </div>
            </div>
        </form>

        <StickyActionBar>
            <Button
                block
                type="submit"
                form="submission"
                :disabled="form.processing"
                >{{ t.save }}</Button
            >
        </StickyActionBar>
    </div>
</template>
