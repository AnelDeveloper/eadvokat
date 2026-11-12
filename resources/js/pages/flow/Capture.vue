<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { bs } from '@/i18n/bs';

defineProps<{ isDemo: boolean }>();

const input = ref<HTMLInputElement | null>(null);
const form = useForm<{ file: File | null }>({ file: null });

function choose(): void {
    if (!form.processing) {
        input.value?.click();
    }
}

function upload(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    form.file = file;
    form.post('/posta/fotografisi', { forceFormData: true });
}
</script>

<template>
    <Head :title="bs.capture.heading" />
    <div class="flex min-h-svh flex-col bg-ink-deep text-white">
        <div class="flex items-center justify-between px-3">
            <Link
                href="/posta"
                class="flex h-11 items-center px-2 text-[15px] text-white no-underline hover:text-white"
                >{{ bs.capture.cancel }}</Link
            >
            <span class="pr-2 text-[13px] text-line-strong">{{
                bs.capture.heading
            }}</span>
        </div>

        <div
            class="relative grid min-h-0 flex-1 place-items-center px-[34px] py-2.5"
        >
            <div
                class="box-border aspect-[1/1.36] max-h-full w-full max-w-[360px] rotate-[-1.2deg] rounded-[3px] bg-white px-5 py-[22px] font-serif text-[10.5px] leading-[1.45] text-ink shadow-capture"
            >
                <template v-if="isDemo">
                    <div class="text-center font-semibold">
                        OPĆINSKI SUD U SARAJEVU
                    </div>
                    <div class="mt-2">
                        Broj: 65 0 P 084512 25 P<br />Sarajevo, 06.11.2026.
                        godine
                    </div>
                    <div
                        class="mt-3.5 mb-2.5 text-center text-[13px] font-semibold tracking-[.14em]"
                    >
                        RJEŠENJE
                    </div>
                    <div>
                        Tužiocu se, putem punomoćnika, dostavlja nalaz i
                        mišljenje vještaka finansijske struke od 30.10.2026.
                        godine.
                    </div>
                    <div class="mt-[7px]">
                        Poziva se tužilac da se u roku od 15 dana od dana
                        prijema ovog rješenja pismeno izjasni na nalaz i
                        mišljenje vještaka.
                    </div>
                    <div class="mt-[7px]">
                        Protiv ovog rješenja nije dozvoljena posebna žalba.
                    </div>
                    <div class="mt-[18px] text-right">Sudija</div>
                    <div
                        class="mt-3.5 font-mono text-[8.5px] text-proposed-text"
                    >
                        {{ bs.capture.fictional }}
                    </div>
                </template>
            </div>
            <div
                v-if="form.processing"
                class="absolute inset-0 grid place-items-center bg-ink-deep/78 text-card font-medium"
                role="status"
            >
                {{ bs.capture.reading }}
            </div>
        </div>

        <div class="px-[30px] text-center text-[13px] text-line-strong">
            {{ isDemo ? bs.capture.hint : bs.capture.hintReal }}
        </div>
        <div
            v-if="form.errors.file"
            class="mt-2 px-[30px] text-center text-[13px] text-urgent-bg"
        >
            {{ form.errors.file }}
        </div>

        <div class="grid place-items-center pt-4 pb-[26px]">
            <input
                ref="input"
                type="file"
                accept="image/*,application/pdf"
                capture="environment"
                class="sr-only"
                tabindex="-1"
                @change="upload"
            />
            <button
                type="button"
                :aria-label="bs.capture.shutter"
                class="size-[74px] rounded-full border-4 border-white bg-transparent p-1"
                @click="choose"
            >
                <span class="block size-full rounded-full bg-white" />
            </button>
        </div>
    </div>
</template>
