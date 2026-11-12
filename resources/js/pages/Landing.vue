<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { bs } from '@/i18n/bs';
</script>

<template>
    <Head :title="bs.appName" />
    <div class="p-6 font-serif text-[24px] font-semibold">{{ bs.appName }}</div>
</template>
