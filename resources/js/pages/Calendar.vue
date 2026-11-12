<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ListRow from '@/components/ui/ListRow.vue';
import { bs } from '@/i18n/bs';
import type { AgendaRow } from '@/types';

type Marker = 'event' | 'deadline' | 'urgent' | 'proposed' | 'muted';

const props = defineProps<{
    month: string;
    monthLabel: string;
    prevMonth: string;
    nextMonth: string;
    offset: number;
    days: Array<{ day: number; isToday: boolean; markers: Marker[] }>;
    selectedDay: number;
    selectedLabel: string;
    rows: AgendaRow[];
}>();

const dayHref = (day: number) => `/kalendar?mjesec=${props.month}&dan=${day}`;

function markerClass(marker: Marker, selected: boolean): string {
    if (marker === 'proposed') {
        return selected
            ? 'border-[1.5px] border-proposed-bg'
            : 'border-[1.5px] border-proposed';
    }

    const fill: Record<Exclude<Marker, 'proposed'>, string> = {
        event: selected ? 'bg-white' : 'bg-ink',
        deadline: selected ? 'bg-white' : 'bg-confirmed',
        urgent: selected ? 'bg-white' : 'bg-urgent',
        muted: 'bg-faint',
    };

    return fill[marker];
}
</script>

<template>
    <Head :title="bs.tabs.calendar" />
    <div class="px-4.5 pt-1.5 pb-4.5">
        <div class="mt-1.5 flex items-center justify-between">
            <h1 class="m-0 text-[26px] leading-[1.1] font-semibold">
                {{ monthLabel }}
            </h1>
            <div class="flex gap-1.5">
                <Link
                    :href="`/kalendar?mjesec=${prevMonth}`"
                    :aria-label="bs.calendar.prev"
                    class="flex size-11 items-center justify-center rounded-field border border-line-strong bg-white text-[20px] text-ink no-underline"
                    >‹</Link
                >
                <Link
                    :href="`/kalendar?mjesec=${nextMonth}`"
                    :aria-label="bs.calendar.next"
                    class="flex size-11 items-center justify-center rounded-field border border-line-strong bg-white text-[20px] text-ink no-underline"
                    >›</Link
                >
            </div>
        </div>

        <div
            class="mt-2.5 grid grid-cols-7 text-center text-label font-semibold text-muted"
            aria-hidden="true"
        >
            <span v-for="d in bs.calendar.weekdays" :key="d">{{ d }}</span>
        </div>
        <div class="mt-1 grid grid-cols-7 gap-0.5">
            <span v-for="n in offset" :key="`blank-${n}`" />
            <Link
                v-for="d in days"
                :key="d.day"
                :href="dayHref(d.day)"
                preserve-scroll
                :aria-current="d.day === selectedDay ? 'date' : undefined"
                class="flex h-[46px] flex-col items-center justify-center gap-1 rounded-field border no-underline"
                :class="[
                    d.day === selectedDay
                        ? 'border-transparent bg-ink text-white hover:text-white'
                        : 'text-ink hover:text-ink',
                    d.isToday && d.day !== selectedDay
                        ? 'border-ink'
                        : d.day !== selectedDay
                          ? 'border-transparent'
                          : '',
                ]"
            >
                <span
                    class="text-[15px] leading-none"
                    :class="d.isToday ? 'font-semibold' : 'font-normal'"
                    >{{ d.day }}</span
                >
                <span class="flex h-1.5 gap-[3px]" aria-hidden="true">
                    <span
                        v-for="(m, i) in d.markers"
                        :key="i"
                        class="box-border size-1.5 rounded-full"
                        :class="markerClass(m, d.day === selectedDay)"
                    />
                </span>
            </Link>
        </div>

        <div
            class="mt-2.5 flex flex-wrap gap-x-3 gap-y-1 text-field text-muted"
        >
            <span class="flex items-center gap-[5px]"
                ><span class="size-[7px] rounded-full bg-ink" />{{
                    bs.calendar.legend.event
                }}</span
            >
            <span class="flex items-center gap-[5px]"
                ><span class="size-[7px] rounded-full bg-confirmed" />{{
                    bs.calendar.legend.deadline
                }}</span
            >
            <span class="flex items-center gap-[5px]"
                ><span
                    class="box-border size-[7px] rounded-full border-[1.5px] border-proposed"
                />{{ bs.calendar.legend.proposed }}</span
            >
            <span class="flex items-center gap-[5px]"
                ><span class="size-[7px] rounded-full bg-faint" />{{
                    bs.calendar.legend.reminder
                }}</span
            >
        </div>

        <section
            class="mt-3.5 overflow-hidden rounded-card border border-line bg-white"
        >
            <h2
                class="m-0 border-b-2 border-ink px-3.5 py-2.5 text-[15px] font-semibold"
            >
                {{ selectedLabel }}
            </h2>
            <div v-if="rows.length === 0" class="p-3.5 text-[14px] text-muted">
                {{ bs.calendar.empty }}
            </div>
            <ListRow
                v-for="row in rows"
                :key="row.id"
                :when="row.when"
                :type="row.type ?? undefined"
                :title="row.title"
                :sub="row.sub"
                :status="row.status"
                :href="row.href ?? undefined"
                :chevron="false"
                divider="bottom"
            />
        </section>
    </div>
</template>
