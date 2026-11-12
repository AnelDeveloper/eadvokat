<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/Button.vue';
import SegmentedControl from '@/components/ui/SegmentedControl.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import { bs } from '@/i18n/bs';

const props = defineProps<{
    direction: 'in' | 'out';
    entries: Array<{
        id: number;
        number: string;
        date: string;
        title: string;
        sub: string;
        link: string | null;
        linkTone: 'confirmed' | 'proposed' | null;
        documentId: number | null;
    }>;
}>();

const direction = computed({
    get: () => props.direction,
    set: (value: 'in' | 'out') =>
        router.get('/posta', value === 'out' ? { smjer: 'izlazna' } : {}, {
            preserveState: false,
        }),
});

const options = [
    { value: 'in', label: bs.mail.incoming },
    { value: 'out', label: bs.mail.outgoing },
] as const;
</script>

<template>
    <Head :title="bs.mail.title" />

    <div class="flex flex-1 flex-col">
        <div class="flex-1 px-4.5 pt-1.5 pb-4.5">
            <h1 class="mt-2 mb-3 text-title font-semibold">
                {{ bs.mail.title }}
            </h1>
            <SegmentedControl
                v-model="direction"
                :options="options"
                text-size="text-[14.5px]"
                :label="bs.mail.title"
            />
            <div class="mt-3 border-t-2 border-ink">
                <component
                    :is="entry.documentId ? Link : 'div'"
                    v-for="entry in entries"
                    :key="entry.id"
                    :href="
                        entry.documentId
                            ? `/dokumenti/${entry.documentId}`
                            : undefined
                    "
                    class="block border-b border-line px-0.5 py-3 text-left text-ink no-underline hover:text-ink"
                >
                    <span
                        class="flex justify-between font-mono text-sub text-muted"
                        ><span>{{ entry.number }}</span
                        ><span>{{ entry.date }}</span></span
                    >
                    <span
                        class="mt-[3px] block text-action leading-[1.3] font-medium"
                        >{{ entry.title }}</span
                    >
                    <span class="mt-0.5 block text-sub text-muted">{{
                        entry.sub
                    }}</span>
                    <span
                        v-if="entry.link"
                        class="mt-0.5 block text-sub"
                        :class="
                            entry.linkTone === 'confirmed'
                                ? 'text-confirmed-text'
                                : 'text-proposed-text'
                        "
                        >{{ entry.link }}</span
                    >
                    <span
                        v-if="entry.documentId"
                        class="mt-1.5 block text-[13px] font-semibold underline"
                        >{{ bs.common.sourceDocument }}</span
                    >
                </component>
                <p
                    v-if="entries.length === 0"
                    class="py-3 text-[14px] text-muted"
                >
                    {{ bs.mail.empty }}
                </p>
            </div>
        </div>
        <StickyActionBar>
            <Button
                block
                :href="
                    direction === 'in'
                        ? '/posta/fotografisi'
                        : '/posta/podnesak'
                "
                >{{
                    direction === 'in'
                        ? bs.mail.captureIncoming
                        : bs.mail.recordOutgoing
                }}</Button
            >
        </StickyActionBar>
    </div>
</template>
