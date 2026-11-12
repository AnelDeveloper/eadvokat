<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';
import { store } from '@/routes/login';

defineProps<{ status?: string }>();

const form = useForm({ email: '', password: '', remember: false });

function submit(): void {
    form.submit(store(), { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head :title="bs.auth.loginTitle" />

    <div class="rounded-card border border-line bg-white px-4.5 py-5">
        <h1 class="m-0 text-title font-semibold">{{ bs.auth.loginTitle }}</h1>
        <p class="mt-1.5 mb-0 text-[14px] text-muted">
            {{ bs.auth.loginDescription }}
        </p>

        <p v-if="status" class="mt-3 text-[14px] text-confirmed-text">
            {{ status }}
        </p>

        <form class="mt-4 grid gap-3" @submit.prevent="submit">
            <TextField
                v-model="form.email"
                :label="bs.auth.email"
                type="email"
                name="email"
                autocomplete="email"
                :error="form.errors.email"
            />
            <TextField
                v-model="form.password"
                :label="bs.auth.password"
                type="password"
                name="password"
                autocomplete="current-password"
                :error="form.errors.password"
            />
            <Checkbox v-model="form.remember">{{ bs.auth.remember }}</Checkbox>
            <Button type="submit" block :disabled="form.processing">{{
                bs.auth.submit
            }}</Button>
        </form>
    </div>
    <p class="mt-3.5 text-[13px] text-muted">{{ bs.auth.noAccount }}</p>
</template>
