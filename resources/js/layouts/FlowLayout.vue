<script setup lang="ts">
import Toast from '@/components/ui/Toast.vue';
</script>

<template>
    <!-- Full-screen flows: no tab bar or sidebar. -->
    <div class="min-h-svh bg-app [--rk-bar-offset:0px]">
        <main class="mx-auto flex min-h-svh max-w-[720px] flex-col">
            <slot />
        </main>
        <Toast />
    </div>
</template>
