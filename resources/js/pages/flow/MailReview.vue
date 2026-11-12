<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/Button.vue';
import DateField from '@/components/ui/DateField.vue';
import ScreenBar from '@/components/ui/ScreenBar.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import SourceQuote from '@/components/ui/SourceQuote.vue';
import StatusChip from '@/components/ui/StatusChip.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';

const props = defineProps<{
    documentId: number;
    paragraphs: Array<{
        text: string;
        align?: string;
        bold?: boolean;
        highlighted?: boolean;
    }>;
    quotes: Array<{ text: string; note: string }>;
    registerNumber: string;
    fields: {
        court: string;
        case_number: string;
        doc_type: string;
        document_date: string | null;
        received_on: string | null;
    };
    receivedOnAssumed: boolean;
}>();

const form = useForm({ ...props.fields });

// The receipt date is an assumption until the lawyer changes it.
const receivedAssumed = computed(
    () =>
        props.receivedOnAssumed &&
        form.received_on === props.fields.received_on,
);

function save(): void {
    form.post(`/posta/pregled/${props.documentId}`);
}
</script>

<template>
    <Head :title="bs.review.title" />
    <div class="flex min-h-svh flex-col">
        <ScreenBar
            :title="bs.review.title"
            back-url="/posta"
            :step="bs.review.step"
        />

        <div class="flex-1 px-4.5 pt-3.5 pb-4.5">
            <SectionLabel>{{ bs.review.source }}</SectionLabel>
            <div
                class="mt-1.5 max-h-[210px] overflow-auto rounded-chip border border-line-strong bg-white px-4 py-3.5 font-serif text-note leading-[1.45]"
            >
                <p
                    v-for="(p, i) in paragraphs"
                    :key="i"
                    class="mt-0 mb-[7px]"
                    :class="[
                        p.align === 'center'
                            ? 'text-center'
                            : p.align === 'right'
                              ? 'text-right'
                              : 'text-left',
                        p.bold ? 'font-semibold' : 'font-normal',
                        p.highlighted && 'bg-proposed-bg shadow-highlight',
                    ]"
                >
                    {{ p.text }}
                </p>
            </div>
            <div class="mt-[5px] text-field text-muted">
                {{ bs.review.sourceHint }}
            </div>

            <div class="mt-4 flex items-center justify-between">
                <h2 class="m-0 text-[17px] font-semibold">
                    {{ bs.review.heading }}
                </h2>
                <StatusChip status="proposed" />
            </div>
            <div class="mt-[3px] font-mono text-sub text-muted">
                {{ registerNumber }} · {{ bs.review.incoming }}
            </div>

            <form
                id="mail-review"
                class="mt-2.5 grid gap-2.5"
                @submit.prevent="save"
            >
                <TextField
                    v-model="form.court"
                    :label="bs.review.court"
                    :error="form.errors.court"
                />
                <TextField
                    v-model="form.case_number"
                    :label="bs.review.caseNumber"
                    :error="form.errors.case_number"
                />
                <TextField
                    v-model="form.doc_type"
                    :label="bs.review.docType"
                    :error="form.errors.doc_type"
                />
                <DateField
                    v-model="form.document_date"
                    :label="bs.review.documentDate"
                    :mono="false"
                    :large="false"
                    :error="form.errors.document_date"
                />
                <DateField
                    v-model="form.received_on"
                    :label="bs.review.receivedOn"
                    :mono="false"
                    :large="false"
                    :warn="receivedAssumed"
                    :note="
                        receivedAssumed ? bs.review.receivedAssumed : undefined
                    "
                    :error="form.errors.received_on"
                />
            </form>

            <SectionLabel class="mt-3.5">{{ bs.review.quotes }}</SectionLabel>
            <SourceQuote
                v-for="(quote, i) in quotes"
                :key="i"
                variant="excerpt"
                :text="quote.text"
                :note="quote.note"
                :class="i === 0 ? 'mt-1.5' : 'mt-2'"
            />
        </div>

        <StickyActionBar>
            <Button
                block
                type="submit"
                form="mail-review"
                :disabled="form.processing"
                >{{ bs.review.save }}</Button
            >
        </StickyActionBar>
    </div>
</template>
