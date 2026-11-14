<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MatterHeader from '@/components/matters/MatterHeader.vue';
import TimelineRow from '@/components/matters/TimelineRow.vue';
import Button from '@/components/ui/Button.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import { bs } from '@/i18n/bs';
import type { AgendaRow, MatterHeader as Header } from '@/types';

defineProps<{ matter: Header; upcoming: AgendaRow[]; recorded: AgendaRow[] }>();
</script>

<template>
    <Head :title="matter.title" />
    <MatterHeader :matter="matter" active="timeline" />

    <div class="px-4.5 pt-1.5 pb-5 lg:px-8">
        <div class="flex items-center justify-between gap-3 pt-2">
            <SectionLabel>{{ bs.matters.upcoming }}</SectionLabel>
            <Button
                :href="`/predmeti/${matter.id}/rokovi/novi`"
                variant="subtle"
                size="sm"
                weight="medium"
                >{{ bs.matters.addDeadline }}</Button
            >
        </div>
        <div class="h-2" />
        <TimelineRow v-for="row in upcoming" :key="row.id" :row="row" />
        <p v-if="upcoming.length === 0" class="m-0 pb-2 text-[14px] text-muted">
            {{
                recorded.length === 0
                    ? bs.matters.emptyMatter
                    : bs.matters.empty
            }}
        </p>
        <SectionLabel v-if="recorded.length > 0" class="pt-3.5 pb-2">{{
            bs.matters.recorded
        }}</SectionLabel>
        <TimelineRow v-for="row in recorded" :key="row.id" :row="row" />
    </div>
</template>
