<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { bs } from '@/i18n/bs';
</script>

<template>
    <Head :title="bs.tabs.today" />
    <div class="px-4.5 pt-1.5">
        <h1 class="m-0 text-title font-semibold">{{ bs.tabs.today }}</h1>
    </div>
</template>
