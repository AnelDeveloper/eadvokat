<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { bs } from '@/i18n/bs';
import { logout } from '@/routes';

defineProps<{ office: string; endedOn: string | null }>();

const t = bs.trial;
</script>

<template>
    <Head :title="t.title" />
    <div class="rounded-card border border-line bg-white px-5 py-6 shadow-card">
        <p class="m-0 text-[14px] font-medium text-muted">{{ office }}</p>
        <h1 class="mt-1 mb-0 text-title font-semibold">{{ t.title }}</h1>
        <p class="mt-2 mb-0 text-[15px] leading-[1.5] text-body">
            {{ t.text }}
        </p>
        <p v-if="endedOn" class="mt-2 mb-0 text-[14px] text-muted">
            {{ t.endedOn(endedOn) }}
        </p>
        <Link
            :href="logout()"
            as="button"
            class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-field border border-line-strong bg-white px-[18px] text-[15px] font-semibold text-ink hover:border-accent"
            >{{ t.logout }}</Link
        >
    </div>
</template>
