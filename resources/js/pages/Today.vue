<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BriefingCard from '@/components/today/BriefingCard.vue';
import QuickActions from '@/components/today/QuickActions.vue';
import TodayHeader from '@/components/today/TodayHeader.vue';
import Button from '@/components/ui/Button.vue';
import Icon from '@/components/ui/Icon.vue';
import { bs } from '@/i18n/bs';
import type { BriefingGroup, BriefingRow } from '@/types';

defineProps<{
    date: string;
    lawyer: string;
    groups: BriefingGroup[];
    canDictate: boolean;
    hasMatters: boolean;
}>();

// On phones there is no sidebar, so the trial countdown sits under the date.
const trialDaysLeft = computed(
    () => usePage().props.auth.user?.trialDaysLeft ?? null,
);

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
            <span
                v-if="trialDaysLeft !== null"
                class="mt-2 inline-block rounded-full bg-accent-bg px-2.5 py-0.5 text-[12.5px] font-semibold text-accent-text lg:hidden"
                >{{ bs.trial.daysLeft(trialDaysLeft) }}</span
            >
            <BriefingCard
                class="mt-3.5"
                :lawyer="lawyer"
                :groups="groups"
                @toggle="toggle"
            />
            <div
                v-if="!hasMatters"
                class="mt-3.5 rounded-card border border-dashed border-line-strong bg-white px-4 py-4"
            >
                <p class="m-0 text-row font-semibold">
                    {{ bs.today.startTitle }}
                </p>
                <p class="mt-1 mb-3 text-[14px] leading-[1.45] text-muted">
                    {{ bs.today.startText }}
                </p>
                <Button href="/predmeti/novi" size="nav"
                    ><Icon name="plus" :size="18" />{{
                        bs.today.startCta
                    }}</Button
                >
            </div>
        </div>
        <QuickActions
            capture-href="/posta/fotografisi"
            :dictate-href="canDictate ? '/diktat' : undefined"
            submission-href="/posta/podnesak"
        />
    </div>
</template>
