<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/Button.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';
import { logout } from '@/routes';
import { update as updatePassword } from '@/routes/settings/password';
import { update as updateProfile } from '@/routes/settings/profile';

const user = usePage().props.auth.user!;

const profile = useForm({ name: user.name, email: user.email });
const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function saveProfile(): void {
    profile.submit(updateProfile(), { preserveScroll: true });
}

function savePassword(): void {
    password.submit(updatePassword(), {
        preserveScroll: true,
        onFinish: () => password.reset(),
    });
}
</script>

<template>
    <Head :title="bs.settings.title" />

    <div class="px-4.5 pt-3.5 pb-6 lg:px-8">
        <h1 class="m-0 text-title font-semibold">{{ bs.settings.title }}</h1>

        <form
            class="mt-3.5 grid gap-2.5 rounded-card border border-line bg-white px-3.5 py-3"
            @submit.prevent="saveProfile"
        >
            <SectionLabel>{{ bs.settings.profile }}</SectionLabel>
            <TextField
                v-model="profile.name"
                :label="bs.settings.name"
                name="name"
                autocomplete="name"
                :error="profile.errors.name"
            />
            <TextField
                v-model="profile.email"
                :label="bs.settings.email"
                type="email"
                name="email"
                autocomplete="email"
                :error="profile.errors.email"
            />
            <Button
                type="submit"
                size="sm"
                variant="outline"
                :disabled="profile.processing"
                >{{ bs.settings.saveProfile }}</Button
            >
        </form>

        <form
            class="mt-2.5 grid gap-2.5 rounded-card border border-line bg-white px-3.5 py-3"
            @submit.prevent="savePassword"
        >
            <SectionLabel>{{ bs.settings.password }}</SectionLabel>
            <TextField
                v-model="password.current_password"
                :label="bs.settings.currentPassword"
                type="password"
                autocomplete="current-password"
                :error="password.errors.current_password"
            />
            <TextField
                v-model="password.password"
                :label="bs.settings.newPassword"
                type="password"
                autocomplete="new-password"
                :error="password.errors.password"
            />
            <TextField
                v-model="password.password_confirmation"
                :label="bs.settings.confirmPassword"
                type="password"
                autocomplete="new-password"
            />
            <Button
                type="submit"
                size="sm"
                variant="outline"
                :disabled="password.processing"
                >{{ bs.settings.savePassword }}</Button
            >
        </form>

        <Link
            :href="logout()"
            as="button"
            class="mt-3.5 inline-flex min-h-11 w-full items-center justify-center rounded-field border border-line-strong bg-white text-[15px] font-medium text-ink"
            >{{ bs.settings.logout }}</Link
        >
    </div>
</template>
