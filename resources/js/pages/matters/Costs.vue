<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MatterHeader from '@/components/matters/MatterHeader.vue';
import { bs } from '@/i18n/bs';
import type { MatterHeader as Header } from '@/types';

// Replaced by the full Troškovnik in Phase 7.
defineProps<{ matter: Header }>();
</script>

<template>
    <Head :title="`${bs.matters.tabs.costs} · ${matter.title}`" />
    <MatterHeader :matter="matter" active="costs" />
</template>
