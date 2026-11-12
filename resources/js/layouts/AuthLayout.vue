<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Logo from '@/components/ui/Logo.vue';
import { bs } from '@/i18n/bs';
import { home } from '@/routes';
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-app px-5 py-10"
    >
        <div class="w-full max-w-[400px]">
            <Link
                :href="home()"
                class="mb-6 inline-flex min-h-11 items-center"
                :aria-label="bs.appName"
                ><Logo :height="34"
            /></Link>
            <slot />
        </div>
    </div>
</template>
