<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/Button.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';

const t = bs.register;
const form = useForm({
    office_name: '',
    title: 'adv.',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post('/registracija', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head :title="t.title" />

    <div
        class="rounded-card border border-line bg-white px-4.5 py-5 shadow-card"
    >
        <h1 class="m-0 text-[26px] leading-[1.15] font-semibold">
            {{ t.title }}
        </h1>
        <p class="mt-1.5 mb-0 text-[14px] text-muted">{{ t.description }}</p>

        <form class="mt-4 grid gap-3" @submit.prevent="submit">
            <TextField
                v-model="form.office_name"
                :label="t.officeName"
                :placeholder="t.officePlaceholder"
                name="organization"
                autocomplete="organization"
                :error="form.errors.office_name"
            />
            <div class="grid grid-cols-[96px_minmax(0,1fr)] gap-2">
                <TextField
                    v-model="form.title"
                    :label="t.titleLabel"
                    name="title"
                    :error="form.errors.title"
                />
                <TextField
                    v-model="form.name"
                    :label="t.name"
                    name="name"
                    autocomplete="name"
                    :error="form.errors.name"
                />
            </div>
            <TextField
                v-model="form.email"
                :label="t.email"
                type="email"
                name="email"
                autocomplete="email"
                :error="form.errors.email"
            />
            <TextField
                v-model="form.password"
                :label="t.password"
                type="password"
                name="password"
                autocomplete="new-password"
                :error="form.errors.password"
            />
            <TextField
                v-model="form.password_confirmation"
                :label="t.confirm"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
            />
            <Button type="submit" block :disabled="form.processing">{{
                t.submit
            }}</Button>
        </form>
    </div>
    <p class="mt-3.5 text-[14px] text-muted">
        {{ t.haveAccount }}
        <Link
            href="/login"
            class="inline-flex min-h-11 items-center font-semibold text-ink underline"
            >{{ t.login }}</Link
        >
    </p>
</template>
