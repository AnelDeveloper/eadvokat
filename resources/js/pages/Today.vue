<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import BriefingCard from '@/components/today/BriefingCard.vue';
import QuickActions from '@/components/today/QuickActions.vue';
import TodayHeader from '@/components/today/TodayHeader.vue';
import Button from '@/components/ui/Button.vue';
import Icon from '@/components/ui/Icon.vue';
import { bs } from '@/i18n/bs';
import type { BriefingGroup, BriefingRow } from '@/types';

defineProps<{ date: string; lawyer: string; groups: BriefingGroup[] }>();

function toggle(row: BriefingRow): void {
    if (row.toggleUrl) {
        router.post(row.toggleUrl, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="bs.today.title" />
    <div class="flex flex-1 flex-col">
        <div class="flex-1 px-4.5 pt-1.5 pb-4.5 lg:px-8">
            <TodayHeader :date="date">
                <Button
                    href="/postavke"
                    variant="subtle"
                    size="sm"
                    weight="medium"
                    class="text-[13px] lg:hidden"
                    ><Icon name="settings" :size="17" class="text-muted" />{{
                        bs.today.settings
                    }}</Button
                >
            </TodayHeader>
            <BriefingCard
                class="mt-3.5"
                :lawyer="lawyer"
                :groups="groups"
                @toggle="toggle"
            />
        </div>
        <QuickActions
            capture-href="/posta/fotografisi"
            dictate-href="/diktat"
            submission-href="/posta/podnesak"
        />
    </div>
</template>
