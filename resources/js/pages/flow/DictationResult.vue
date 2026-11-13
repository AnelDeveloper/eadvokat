<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import DateField from '@/components/ui/DateField.vue';
import ScreenBar from '@/components/ui/ScreenBar.vue';
import SectionLabel from '@/components/ui/SectionLabel.vue';
import StatusChip from '@/components/ui/StatusChip.vue';
import StickyActionBar from '@/components/ui/StickyActionBar.vue';
import TextField from '@/components/ui/TextField.vue';
import { showToast } from '@/composables/useToast';
import { bs } from '@/i18n/bs';
import { formatDate } from '@/lib/format';

type Saved = {
    event: {
        id: number;
        date: string;
        time: string;
        room: string | null;
        confirmed: boolean;
    } | null;
    deadline: {
        id: number;
        dueDate: string;
        status: string;
        confirmed: boolean;
    } | null;
    note: { id: number; body: string; confirmed: boolean } | null;
};

const props = defineProps<{
    documentId: number;
    proposal: {
        transcript: string;
        event: {
            title: string;
            date: string;
            time: string;
            heard: string;
            options: string[];
        };
        deadline: {
            title: string;
            ruleText: string;
            highlight: string;
            days: number;
            startDate: string;
            dueDate: string;
        };
        note: string;
        hearingDate: string;
    };
    saved: Saved | null;
}>();

const t = bs.dictation;
const p = props.proposal;
const saved = computed(() => props.saved);

// Draft state: every card is an independent proposal; editing a field resets it.
const draft = reactive({
    event: {
        date: (props.saved?.event?.date ?? p.event.date) as string | null,
        time: props.saved?.event?.time ?? p.event.time,
        room: (props.saved?.event?.room ?? null) as string | null,
        confirmed: false,
    },
    deadline: {
        dueDate: p.deadline.dueDate as string | null,
        ruleAck: false,
        startAck: false,
        dueAck: false,
        confirmed: false,
    },
    note: { body: props.saved?.note?.body ?? p.note, confirmed: false },
});

watch(
    () => [draft.event.date, draft.event.time],
    () => (draft.event.confirmed = false),
);
watch(
    () => draft.deadline.dueDate,
    () => {
        draft.deadline.dueAck = false;
        draft.deadline.confirmed = false;
    },
);
watch(
    () => [
        draft.deadline.ruleAck,
        draft.deadline.startAck,
        draft.deadline.dueAck,
    ],
    (acks) => {
        if (acks.some((a) => !a)) {
            draft.deadline.confirmed = false;
        }
    },
);
watch(
    () => draft.note.body,
    () => (draft.note.confirmed = false),
);

const roomResolved = computed(() => draft.event.room !== null);
const shownRoom = computed(() => draft.event.room ?? p.event.heard);

const transcriptParts = computed(() => {
    const token = `sudnica ${p.event.heard}`;
    const at = p.transcript.indexOf(token);

    return at < 0
        ? { before: p.transcript, room: '', after: '' }
        : {
              before: `${p.transcript.slice(0, at)}sudnica `,
              room: shownRoom.value,
              after: p.transcript.slice(at + token.length),
          };
});

const deadlineAcks = computed(
    () =>
        draft.deadline.ruleAck &&
        draft.deadline.startAck &&
        draft.deadline.dueAck,
);
const confirmedCount = computed(
    () =>
        Number(draft.event.confirmed) +
        Number(draft.deadline.confirmed) +
        Number(draft.note.confirmed),
);

function chooseRoom(room: string): void {
    draft.event.room = room;
    draft.event.confirmed = false;
}

function toggleEvent(): void {
    if (!draft.event.confirmed && !roomResolved.value) {
        showToast(t.roomFirst(p.event.options));

        return;
    }

    draft.event.confirmed = !draft.event.confirmed;
}

function toggleDeadline(): void {
    if (!draft.deadline.confirmed && !deadlineAcks.value) {
        showToast(bs.deadline.needsChecksToast);

        return;
    }

    draft.deadline.confirmed = !draft.deadline.confirmed;
}

const saving = ref(false);

function save(): void {
    if (!draft.event.date || !draft.deadline.dueDate) {
        showToast(bs.common.dateHint);

        return;
    }

    saving.value = true;
    router.post(
        `/diktat/${props.documentId}/sacuvaj`,
        {
            event: {
                date: draft.event.date,
                time: draft.event.time,
                location: draft.event.room,
                confirmed: draft.event.confirmed,
            },
            deadline: {
                due_date: draft.deadline.dueDate,
                rule_ack: draft.deadline.ruleAck,
                start_ack: draft.deadline.startAck,
                due_ack: draft.deadline.dueAck,
                confirmed: draft.deadline.confirmed,
            },
            note: { body: draft.note.body, confirmed: draft.note.confirmed },
        },
        {
            onError: (errors) =>
                showToast(Object.values(errors)[0] ?? t.saveHint),
            onFinish: () => (saving.value = false),
        },
    );
}

// After saving, unconfirmed proposals are confirmed one by one.
const eventForm = useForm({});
const noteForm = useForm({});

function confirmSavedEvent(): void {
    if (!saved.value?.event) {
        return;
    }

    if (!roomResolved.value) {
        showToast(t.roomFirst(p.event.options));

        return;
    }

    eventForm
        .transform(() => ({
            date: draft.event.date,
            time: draft.event.time,
            location: draft.event.room,
        }))
        .post(`/dogadjaji/${saved.value.event.id}/potvrda`, {
            preserveScroll: true,
        });
}

function confirmSavedNote(): void {
    if (saved.value?.note) {
        noteForm
            .transform(() => ({ body: draft.note.body }))
            .post(`/biljeske/${saved.value.note.id}/potvrda`, {
                preserveScroll: true,
            });
    }
}

const eventConfirmed = computed(() =>
    saved.value ? !!saved.value.event?.confirmed : draft.event.confirmed,
);
const deadlineConfirmed = computed(() =>
    saved.value ? !!saved.value.deadline?.confirmed : draft.deadline.confirmed,
);
const noteConfirmed = computed(() =>
    saved.value ? !!saved.value.note?.confirmed : draft.note.confirmed,
);

const toggleClass = (on: boolean) =>
    on
        ? 'border-line-strong bg-white text-ink'
        : 'border-accent bg-accent text-white';
</script>

<template>
    <Head :title="t.title" />
    <div class="flex min-h-svh flex-col">
        <ScreenBar :title="t.title" back-url="/danas" />

        <div class="flex-1 px-4.5 pt-3.5 pb-4.5">
            <SectionLabel>{{ t.transcript }}</SectionLabel>
            <div
                class="mt-1.5 rounded-chip border border-line-strong bg-white px-3.5 py-3 font-serif text-[15.5px] leading-[1.45]"
            >
                „{{ transcriptParts.before
                }}<mark
                    v-if="transcriptParts.room"
                    class="text-ink"
                    :class="
                        roomResolved
                            ? 'bg-transparent'
                            : 'bg-proposed-bg shadow-highlight'
                    "
                    >{{ transcriptParts.room }}</mark
                >{{ transcriptParts.after }}“
            </div>

            <!-- Događaj u kalendaru -->
            <section
                class="mt-3 rounded-card border border-line bg-white px-3.5 py-3 shadow-card"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-field font-medium text-muted">{{
                        t.eventCard
                    }}</span>
                    <StatusChip
                        :status="eventConfirmed ? 'confirmed' : 'proposed'"
                    />
                </div>
                <div class="mt-[3px] text-action font-medium">
                    {{ t.eventTitle(p.event.title) }}
                </div>
                <template v-if="!saved || !eventConfirmed">
                    <div class="mt-2 grid grid-cols-[1.5fr_1fr] gap-1.5">
                        <DateField
                            v-model="draft.event.date"
                            :label="t.date"
                            :large="false"
                        />
                        <TextField
                            v-model="draft.event.time"
                            :label="t.time"
                            mono
                        />
                    </div>
                    <div
                        class="mt-2 text-sub"
                        :class="
                            roomResolved
                                ? 'text-confirmed-text'
                                : 'text-proposed-text'
                        "
                    >
                        {{
                            roomResolved
                                ? t.roomChecked(shownRoom)
                                : t.roomUnclear(p.event.options)
                        }}
                    </div>
                    <div class="mt-[5px] grid grid-cols-2 gap-1.5">
                        <button
                            v-for="room in p.event.options"
                            :key="room"
                            type="button"
                            :aria-pressed="draft.event.room === room"
                            class="h-11 rounded-field border text-[14px] font-semibold"
                            :class="
                                draft.event.room === room
                                    ? 'border-accent bg-accent text-white'
                                    : 'border-line-strong bg-white text-ink'
                            "
                            @click="chooseRoom(room)"
                        >
                            {{ t.room(room) }}
                        </button>
                    </div>
                    <button
                        v-if="!saved"
                        type="button"
                        class="mt-2 h-11 w-full rounded-field border text-[14px] font-semibold"
                        :class="toggleClass(draft.event.confirmed)"
                        @click="toggleEvent"
                    >
                        {{ draft.event.confirmed ? t.confirmed : t.confirm }}
                    </button>
                    <button
                        v-else
                        type="button"
                        class="mt-2 h-11 w-full rounded-field border border-accent bg-accent text-[14px] font-semibold text-white"
                        :disabled="eventForm.processing"
                        @click="confirmSavedEvent"
                    >
                        {{ t.confirm }}
                    </button>
                </template>
            </section>

            <!-- Rok -->
            <section
                class="mt-2 rounded-card border border-line bg-white px-3.5 py-3 shadow-card"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-field font-medium text-muted">{{
                        t.deadlineCard
                    }}</span>
                    <StatusChip
                        :status="deadlineConfirmed ? 'confirmed' : 'proposed'"
                    />
                </div>
                <div class="mt-[3px] text-action font-medium">
                    {{ p.deadline.title }}
                </div>
                <div class="mt-0.5 text-sub text-muted">
                    {{
                        t.deadlineSource(
                            p.deadline.highlight,
                            formatDate(p.deadline.startDate),
                        )
                    }}
                </div>
                <template v-if="!saved">
                    <DateField
                        v-model="draft.deadline.dueDate"
                        class="mt-2"
                        :label="t.dueLabel"
                        :large="false"
                    />
                    <div class="mt-2 text-field font-medium text-muted">
                        {{ p.deadline.ruleText }}
                    </div>
                    <Checkbox v-model="draft.deadline.ruleAck">{{
                        bs.deadline.ruleAck
                    }}</Checkbox>
                    <Checkbox v-model="draft.deadline.startAck">{{
                        bs.deadline.startAck
                    }}</Checkbox>
                    <Checkbox v-model="draft.deadline.dueAck">{{
                        bs.deadline.dueAck
                    }}</Checkbox>
                    <button
                        type="button"
                        class="mt-2 h-11 w-full rounded-field border text-[14px] font-semibold"
                        :class="toggleClass(draft.deadline.confirmed)"
                        @click="toggleDeadline"
                    >
                        {{ draft.deadline.confirmed ? t.confirmed : t.confirm }}
                    </button>
                </template>
                <Button
                    v-else-if="saved.deadline && !saved.deadline.confirmed"
                    :href="`/rokovi/${saved.deadline.id}/prijedlog`"
                    size="sm"
                    block
                    class="mt-2"
                    >{{ t.reviewDeadline }}</Button
                >
            </section>

            <!-- Bilješka u predmetu -->
            <section
                class="mt-2 rounded-card border border-line bg-white px-3.5 py-3 shadow-card"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-field font-medium text-muted">{{
                        t.noteCard
                    }}</span>
                    <StatusChip
                        :status="noteConfirmed ? 'confirmed' : 'proposed'"
                    />
                </div>
                <template v-if="!saved || !noteConfirmed">
                    <textarea
                        v-model="draft.note.body"
                        rows="4"
                        :aria-label="t.noteCard"
                        class="mt-2 box-border w-full resize-none rounded-field border border-line-strong p-2.5 text-[14.5px] leading-[1.4] outline-none focus:border-accent focus:ring-3 focus:ring-accent/15"
                    />
                    <button
                        type="button"
                        class="mt-1.5 h-11 w-full rounded-field border text-[14px] font-semibold"
                        :class="toggleClass(!saved && draft.note.confirmed)"
                        :disabled="noteForm.processing"
                        @click="
                            saved
                                ? confirmSavedNote()
                                : (draft.note.confirmed = !draft.note.confirmed)
                        "
                    >
                        {{
                            !saved && draft.note.confirmed
                                ? t.confirmed
                                : t.confirm
                        }}
                    </button>
                </template>
                <p v-else class="mt-2 mb-0 text-[14.5px] leading-[1.4]">
                    {{ saved.note?.body }}
                </p>
            </section>

            <div class="mt-2.5 text-sub text-muted">
                {{ saved ? t.savedHint : t.costHint(p.hearingDate) }}
            </div>
        </div>

        <StickyActionBar v-if="!saved">
            <Button block :disabled="saving" @click="save">{{
                t.save(confirmedCount)
            }}</Button>
            <div class="mt-1.5 text-center text-field text-muted">
                {{ t.saveHint }}
            </div>
        </StickyActionBar>
    </div>
</template>
