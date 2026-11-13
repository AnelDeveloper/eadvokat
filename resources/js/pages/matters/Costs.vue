<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import MatterHeader from '@/components/matters/MatterHeader.vue';
import Button from '@/components/ui/Button.vue';
import MoneyField from '@/components/ui/MoneyField.vue';
import SegmentedControl from '@/components/ui/SegmentedControl.vue';
import StatusChip from '@/components/ui/StatusChip.vue';
import { showToast } from '@/composables/useToast';
import { bs } from '@/i18n/bs';
import type { MatterHeader as Header } from '@/types';

type Item = {
    id: number;
    title: string;
    date: string;
    source: string;
    documentId: number | null;
    amount: string | null;
    confirmed: boolean;
};

const props = defineProps<{
    matter: Header;
    sheet: {
        id: number;
        approved: boolean;
        jurisdiction: string;
        tariffSource: string;
        pending: number;
        pendingLabel: string | null;
        total: string;
        items: Item[];
    };
    isDemo: boolean;
    approvedLine: string | null;
    jurisdictions: Array<{ value: string; label: string }>;
}>();

const t = bs.costs;
const opts = { preserveScroll: true, preserveState: true } as const;

const jurisdiction = computed({
    get: () => props.sheet.jurisdiction,
    set: (value: string) =>
        router.patch(
            `/predmeti/${props.matter.id}/troskovnik`,
            { jurisdiction: value, tariff_source: tariff.value },
            opts,
        ),
});

const tariff = ref(props.sheet.tariffSource);
watch(
    () => props.sheet.tariffSource,
    (value) => (tariff.value = value),
);

function saveTariff(): void {
    if (tariff.value !== props.sheet.tariffSource) {
        router.patch(
            `/predmeti/${props.matter.id}/troskovnik`,
            {
                jurisdiction: props.sheet.jurisdiction,
                tariff_source: tariff.value,
            },
            opts,
        );
    }
}

function setAmount(item: Item, amount: string | null): void {
    if (amount !== item.amount) {
        router.patch(`/troskovi/stavke/${item.id}`, { amount }, opts);
    }
}

function toggle(item: Item): void {
    if (!item.confirmed && item.amount === null) {
        showToast(t.amountToast);

        return;
    }

    router.post(
        `/troskovi/stavke/${item.id}/${item.confirmed ? 'vrati' : 'potvrda'}`,
        {},
        opts,
    );
}

function remove(item: Item): void {
    router.delete(`/troskovi/stavke/${item.id}`, opts);
}

const allConfirmed = computed(
    () => props.sheet.pending === 0 && props.sheet.items.length > 0,
);

function approve(): void {
    if (props.sheet.approved) {
        return;
    }

    if (!allConfirmed.value) {
        showToast(t.confirmAllToast);

        return;
    }

    if (tariff.value.trim() === '') {
        showToast(t.tariffToast);

        return;
    }

    router.post(`/predmeti/${props.matter.id}/troskovnik/odobri`, {}, opts);
}
</script>

<template>
    <Head :title="`${bs.matters.tabs.costs} · ${matter.title}`" />
    <MatterHeader :matter="matter" active="costs" />

    <div class="px-4.5 pt-3.5 pb-[22px] lg:px-8">
        <div class="flex items-center justify-between gap-2.5">
            <h2 class="m-0 text-matter font-semibold">{{ t.title }}</h2>
            <StatusChip
                :status="sheet.approved ? 'confirmed' : 'proposed'"
                :label="sheet.approved ? t.chipApproved : t.chipDraft"
            />
        </div>
        <div
            class="mt-2.5 rounded-field border border-dashed border-proposed bg-proposed-bg px-3 py-2.5 text-[13px] leading-[1.4] text-proposed-text"
        >
            <template v-if="isDemo"
                >{{ t.demoNotice }} <b>{{ t.demoNoticeBold }}</b
                >{{ t.demoNoticeEnd }}</template
            >
            <template v-else>{{ t.notice }}</template>
        </div>

        <div class="mt-3.5 text-field font-medium text-muted">
            {{ t.jurisdiction }}
        </div>
        <SegmentedControl
            v-model="jurisdiction"
            class="mt-[5px]"
            :options="jurisdictions"
            :label="t.jurisdiction"
        />

        <label class="mt-3 block">
            <span class="text-field font-medium text-muted">{{
                t.tariff
            }}</span>
            <input
                v-model="tariff"
                :placeholder="t.tariffPlaceholder"
                class="mt-[5px] box-border block h-11 w-full rounded-field border border-line-strong bg-white px-3 text-[14px] outline-none focus:border-accent focus:ring-3 focus:ring-accent/15"
                @change="saveTariff"
            />
        </label>
        <div class="mt-[5px] text-field text-muted">
            {{ isDemo ? t.tariffNoteDemo : t.tariffNote }}
        </div>

        <div class="mt-4 border-t border-line">
            <div
                v-for="item in sheet.items"
                :key="item.id"
                class="border-b border-line py-3"
            >
                <div class="flex items-start justify-between gap-2.5">
                    <span class="min-w-0">
                        <span class="block text-row font-medium">{{
                            item.title
                        }}</span>
                        <span
                            class="mt-0.5 block font-mono text-[12px] text-muted"
                            >{{ item.date }} · {{ item.source }}</span
                        >
                    </span>
                    <StatusChip
                        class="flex-none"
                        :status="item.confirmed ? 'confirmed' : 'proposed'"
                    />
                </div>
                <div
                    class="mt-[9px] grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-1.5"
                >
                    <MoneyField
                        :model-value="item.amount"
                        :suffix="isDemo ? t.suffixDemo : t.suffix"
                        :label="item.title"
                        @update:model-value="(v) => setAmount(item, v)"
                    />
                    <button
                        type="button"
                        class="h-11 rounded-field border px-3 text-[13.5px] font-semibold"
                        :class="
                            item.confirmed
                                ? 'border-line-strong bg-white text-ink'
                                : 'border-accent bg-accent text-white'
                        "
                        @click="toggle(item)"
                    >
                        {{ item.confirmed ? t.revert : t.confirm }}
                    </button>
                    <button
                        type="button"
                        :aria-label="t.remove"
                        class="size-11 rounded-field border border-line-strong bg-white text-[18px] text-urgent"
                        @click="remove(item)"
                    >
                        ×
                    </button>
                </div>
            </div>
            <p
                v-if="sheet.items.length === 0"
                class="py-3 text-[14px] text-muted"
            >
                {{ t.empty }}
            </p>
            <div class="flex items-baseline justify-between py-3">
                <span class="text-[14px] font-semibold">{{
                    isDemo ? t.totalDemo : t.total
                }}</span>
                <span class="font-mono text-[18px] font-medium"
                    >{{ sheet.total }} KM</span
                >
            </div>
        </div>

        <Button
            block
            :variant="
                sheet.approved
                    ? 'confirm'
                    : allConfirmed
                      ? 'primary'
                      : 'inactive'
            "
            class="py-1.5"
            @click="approve"
            >{{
                sheet.approved
                    ? t.approved
                    : allConfirmed
                      ? t.approve
                      : t.confirmFirst(sheet.pendingLabel ?? '')
            }}</Button
        >
        <div v-if="approvedLine" class="mt-1.5 text-field text-confirmed-text">
            {{ approvedLine }}
        </div>
        <Button
            block
            anchor
            :href="`/predmeti/${matter.id}/troskovnik/pdf`"
            variant="subtle"
            size="md"
            weight="medium"
            class="mt-2"
            >{{ t.export }}</Button
        >
        <div class="mt-2 text-field text-muted">{{ t.nothingSent }}</div>
    </div>
</template>
