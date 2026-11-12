<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import Button from '@/components/ui/Button.vue';
import ScreenBar from '@/components/ui/ScreenBar.vue';
import { bs } from '@/i18n/bs';

const props = defineProps<{
    hearing: { id: number; label: string } | null;
    isDemo: boolean;
}>();

const t = bs.dictation;
const state = ref<'idle' | 'recording'>('idle');
const seconds = ref(0);
const micError = ref(false);
let recorder: MediaRecorder | null = null;
let chunks: Blob[] = [];
let timer: ReturnType<typeof setInterval> | undefined;

const form = useForm<{ event_id: number | null; audio: File | null }>({
    event_id: props.hearing?.id ?? null,
    audio: null,
});

const clock = computed(
    () =>
        `${Math.floor(seconds.value / 60)}:${String(seconds.value % 60).padStart(2, '0')}`,
);

async function start(): Promise<void> {
    micError.value = false;

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            audio: true,
        });
        chunks = [];
        recorder = new MediaRecorder(stream);
        recorder.ondataavailable = (event) => chunks.push(event.data);
        recorder.onstop = () => {
            stream.getTracks().forEach((track) => track.stop());
            const type = recorder?.mimeType || 'audio/webm';
            const extension = type.includes('mp4')
                ? 'm4a'
                : type.includes('ogg')
                  ? 'ogg'
                  : 'webm';
            send(
                new File(chunks, `diktat.${extension}`, {
                    type: type.split(';')[0],
                }),
            );
        };
        recorder.start();
        seconds.value = 0;
        timer = setInterval(() => seconds.value++, 1000);
        state.value = 'recording';
    } catch {
        micError.value = true;
    }
}

function stop(): void {
    clearInterval(timer);
    state.value = 'idle';
    recorder?.stop();
}

function send(audio: File | null): void {
    form.audio = audio;
    form.post('/diktat', { forceFormData: true });
}

function toggle(): void {
    if (form.processing) {
        return;
    }

    if (state.value === 'idle') {
        void start();
    } else {
        stop();
    }
}

onBeforeUnmount(() => {
    clearInterval(timer);

    if (recorder && recorder.state !== 'inactive') {
        recorder.onstop = null;
        recorder.stop();
    }
});
</script>

<template>
    <Head :title="t.title" />
    <div class="flex min-h-svh flex-col">
        <ScreenBar :title="t.title" back-url="/danas" />

        <div
            class="flex flex-1 flex-col items-center justify-center gap-[18px] p-6 text-center"
        >
            <template v-if="hearing">
                <div class="font-mono text-sub text-muted">
                    {{ hearing.label }}
                </div>
                <button
                    type="button"
                    :aria-label="t.micLabel"
                    :aria-pressed="state === 'recording'"
                    class="grid size-[112px] place-items-center rounded-full border-0"
                    :class="state === 'recording' ? 'bg-urgent' : 'bg-ink'"
                    :disabled="form.processing"
                    @click="toggle"
                >
                    <span class="flex flex-col items-center" aria-hidden="true">
                        <span class="h-[34px] w-5 rounded-[10px] bg-white" />
                        <span
                            class="-mt-2 box-border h-3 w-[34px] rounded-b-[17px] border-[3px] border-t-0 border-white"
                        />
                        <span class="h-2 w-[3px] bg-white" />
                    </span>
                </button>
                <div
                    class="flex items-center gap-2 text-[18px] font-semibold"
                    role="status"
                >
                    <span
                        v-if="state === 'recording'"
                        class="size-2.5 animate-pulse-dot rounded-full bg-urgent"
                    />
                    {{
                        form.processing
                            ? t.processing
                            : state === 'recording'
                              ? t.recording(clock)
                              : t.idle
                    }}
                </div>
                <div class="max-w-[280px] text-[14px] text-muted">
                    {{
                        state === 'recording'
                            ? isDemo
                                ? t.recordingHint
                                : t.recordingHintReal
                            : t.idleHint
                    }}
                </div>
                <div
                    v-if="micError"
                    class="max-w-[300px] text-[14px] text-urgent-text"
                >
                    {{ t.noMic }}
                </div>
                <Button
                    v-if="micError && isDemo"
                    variant="outline"
                    size="sm"
                    :disabled="form.processing"
                    @click="send(null)"
                    >{{ t.useFixture }}</Button
                >
            </template>
            <p v-else class="text-[15px] text-muted">{{ t.noHearing }}</p>
        </div>
    </div>
</template>
