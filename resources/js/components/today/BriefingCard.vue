<script setup lang="ts">
import ListRow from '@/components/ui/ListRow.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import { bs } from '@/i18n/bs';
import type { BriefingGroup, BriefingRow } from '@/types';

const { interactive = true } = defineProps<{
    lawyer: string;
    groups: BriefingGroup[];
    interactive?: boolean;
}>();

const emit = defineEmits<{ toggle: [row: BriefingRow] }>();
</script>

<template>
    <section
        class="overflow-hidden rounded-card border border-line bg-white"
        :aria-label="bs.today.briefing"
    >
        <div
            class="flex items-baseline justify-between border-b-2 border-ink px-3.5 py-[11px]"
        >
            <span class="text-card font-semibold">{{ bs.today.briefing }}</span>
            <span class="font-mono text-[12px] text-muted">{{ lawyer }}</span>
        </div>
        <template v-for="group in groups" :key="group.key">
            <SectionLabel band>{{ bs.today.groups[group.key] }}</SectionLabel>
            <div
                v-if="group.key === 'deadlines' && group.rows.length === 0"
                class="border-t border-hairline px-3.5 py-2.5 text-note text-muted"
            >
                {{ bs.today.noDeadlines }}
            </div>
            <ListRow
                v-for="row in group.rows"
                :key="row.id"
                :when="row.when"
                :title="row.title"
                :sub="row.sub"
                :status="row.status"
                :label="row.label"
                :href="row.toggleUrl ? undefined : row.href"
                :interactive="interactive"
                @click="emit('toggle', row)"
            />
        </template>
    </section>
</template>
