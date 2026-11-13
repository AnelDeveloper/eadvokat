<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/Button.vue';
import TextField from '@/components/ui/TextField.vue';
import { bs } from '@/i18n/bs';
import { logout } from '@/routes';
import { update as updatePassword } from '@/routes/settings/password';
import { update as updateProfile } from '@/routes/settings/profile';

const page = usePage();
const user = computed(() => page.props.auth.user!);

const profile = useForm({ name: user.value.name, email: user.value.email });
const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordFilled = computed(
    () =>
        password.current_password !== '' &&
        password.password !== '' &&
        password.password_confirmation !== '',
);

function saveProfile(): void {
    profile.submit(updateProfile(), {
        preserveScroll: true,
        onSuccess: () => profile.defaults(),
    });
}

function savePassword(): void {
    password.submit(updatePassword(), {
        preserveScroll: true,
        onFinish: () => password.reset(),
    });
}

const section =
    'grid gap-3 border-t border-line py-6 first:border-t-0 lg:grid-cols-[minmax(0,280px)_minmax(0,1fr)] lg:gap-10 lg:py-8';
const card = 'rounded-card border border-line bg-white shadow-card';
</script>

<template>
    <Head :title="bs.settings.title" />

    <div class="px-4.5 pt-1.5 pb-8 lg:px-8 lg:pt-8">
        <Link
            href="/danas"
            class="inline-flex h-11 items-center text-[15px] font-medium text-ink no-underline lg:hidden"
            >{{ bs.settings.back }}</Link
        >
        <h1 class="mt-0 mb-1 text-title font-semibold">
            {{ bs.settings.title }}
        </h1>
        <p class="m-0 text-[15px] text-muted">{{ bs.settings.intro }}</p>

        <div class="mt-4 lg:mt-6">
            <!-- Profile -->
            <section :class="section" aria-labelledby="s-profile">
                <div>
                    <h2 id="s-profile" class="m-0 text-[17px] font-semibold">
                        {{ bs.settings.profile }}
                    </h2>
                    <p class="mt-1 mb-0 text-[14px] leading-[1.45] text-muted">
                        {{ bs.settings.profileHelp }}
                    </p>
                </div>
                <form :class="card" @submit.prevent="saveProfile">
                    <div class="grid gap-4 px-4 pt-4 pb-5 sm:px-5">
                        <div>
                            <span class="text-field font-medium text-muted">{{
                                bs.settings.office
                            }}</span>
                            <p
                                class="mt-1 mb-0 flex min-h-11 items-center rounded-field bg-app px-3 text-[15px] text-ink"
                            >
                                {{ user.office }}
                            </p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
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
                        </div>
                    </div>
                    <div
                        class="flex flex-col-reverse items-stretch gap-2 border-t border-line bg-app/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-end sm:gap-4 sm:px-5"
                    >
                        <span
                            v-if="profile.recentlySuccessful"
                            class="text-center text-[14px] font-medium text-confirmed-text"
                            role="status"
                            >✓ {{ bs.settings.saved }}</span
                        >
                        <Button
                            type="submit"
                            size="nav"
                            :disabled="!profile.isDirty || profile.processing"
                            >{{ bs.settings.saveProfile }}</Button
                        >
                    </div>
                </form>
            </section>

            <!-- Password -->
            <section :class="section" aria-labelledby="s-password">
                <div>
                    <h2 id="s-password" class="m-0 text-[17px] font-semibold">
                        {{ bs.settings.password }}
                    </h2>
                    <p class="mt-1 mb-0 text-[14px] leading-[1.45] text-muted">
                        {{ bs.settings.passwordHelp }}
                    </p>
                </div>
                <form :class="card" @submit.prevent="savePassword">
                    <!-- Hidden username helps password managers pair the new password with the account. -->
                    <input
                        type="email"
                        name="username"
                        autocomplete="username"
                        :value="user.email"
                        class="hidden"
                        readonly
                        tabindex="-1"
                        aria-hidden="true"
                    />
                    <div class="grid gap-4 px-4 pt-4 pb-5 sm:px-5">
                        <TextField
                            v-model="password.current_password"
                            class="sm:max-w-[calc(50%-8px)]"
                            :label="bs.settings.currentPassword"
                            type="password"
                            name="current_password"
                            autocomplete="current-password"
                            :error="password.errors.current_password"
                        />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <TextField
                                v-model="password.password"
                                :label="bs.settings.newPassword"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                :error="password.errors.password"
                            />
                            <TextField
                                v-model="password.password_confirmation"
                                :label="bs.settings.confirmPassword"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>
                    <div
                        class="flex flex-col-reverse items-stretch gap-2 border-t border-line bg-app/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-end sm:gap-4 sm:px-5"
                    >
                        <span
                            v-if="password.recentlySuccessful"
                            class="text-center text-[14px] font-medium text-confirmed-text"
                            role="status"
                            >✓ {{ bs.settings.saved }}</span
                        >
                        <Button
                            type="submit"
                            size="nav"
                            :disabled="!passwordFilled || password.processing"
                            >{{ bs.settings.savePassword }}</Button
                        >
                    </div>
                </form>
            </section>

            <!-- Logout -->
            <section :class="section" aria-labelledby="s-logout">
                <div>
                    <h2 id="s-logout" class="m-0 text-[17px] font-semibold">
                        {{ bs.settings.logoutTitle }}
                    </h2>
                    <p class="mt-1 mb-0 text-[14px] leading-[1.45] text-muted">
                        {{ bs.settings.logoutHelp }}
                    </p>
                </div>
                <div class="lg:pt-0.5">
                    <Link
                        :href="logout()"
                        as="button"
                        class="inline-flex min-h-11 w-full items-center justify-center rounded-field border border-urgent/40 bg-white px-[18px] text-[15px] font-semibold text-urgent-text hover:bg-urgent-bg sm:w-auto"
                        >{{ bs.settings.logout }}</Link
                    >
                </div>
            </section>
        </div>
    </div>
</template>
