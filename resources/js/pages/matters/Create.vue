<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/Button.vue';
import SegmentedControl from '@/components/ui/SegmentedControl.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';

defineProps<{ jurisdictions: Array<{ value: string; label: string }> }>();

const t = bs.newMatter;
const form = useForm({
    case_number: '',
    title: '',
    subject: '',
    court: '',
    represented_side: '',
    client_name: '',
    jurisdiction: 'fbih',
});
</script>

<template>
    <Head :title="t.title" />
    <div class="px-4.5 pt-1.5 pb-6 lg:px-8">
        <Link
            href="/predmeti"
            class="inline-flex h-11 items-center text-[15px] font-medium text-ink no-underline"
            >{{ bs.matters.back }}</Link
        >
        <h1 class="m-0 text-title font-semibold">{{ t.title }}</h1>
        <form
            class="mt-3.5 grid max-w-[720px] gap-3 rounded-card border border-line bg-white px-3.5 py-4 shadow-card"
            @submit.prevent="form.post('/predmeti')"
        >
            <TextField
                v-model="form.case_number"
                :label="t.caseNumber"
                :placeholder="t.caseNumberPlaceholder"
                mono
                :error="form.errors.case_number"
            />
            <TextField
                v-model="form.title"
                :label="t.name"
                :placeholder="t.namePlaceholder"
                :error="form.errors.title"
            />
            <TextField
                v-model="form.subject"
                :label="t.subject"
                :placeholder="t.subjectPlaceholder"
                :error="form.errors.subject"
            />
            <TextField
                v-model="form.court"
                :label="t.court"
                :placeholder="t.courtPlaceholder"
                :error="form.errors.court"
            />
            <div class="grid gap-3 sm:grid-cols-2">
                <TextField
                    v-model="form.represented_side"
                    :label="t.side"
                    :placeholder="t.sidePlaceholder"
                    :error="form.errors.represented_side"
                />
                <TextField
                    v-model="form.client_name"
                    :label="t.client"
                    :error="form.errors.client_name"
                />
            </div>
            <div>
                <div class="text-field font-medium text-muted">
                    {{ t.jurisdiction }}
                </div>
                <SegmentedControl
                    v-model="form.jurisdiction"
                    class="mt-1"
                    :options="jurisdictions"
                    :label="t.jurisdiction"
                />
            </div>
            <Button type="submit" block :disabled="form.processing">{{
                t.submit
            }}</Button>
        </form>
    </div>
</template>
