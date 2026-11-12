<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import BottomSheet from '@/components/ui/BottomSheet.vue';
import Button from '@/components/ui/Button.vue';
import Logo from '@/components/ui/Logo.vue';
import PhoneFrame from '@/components/ui/PhoneFrame.vue';
import PhoneStatusBar from '@/components/ui/PhoneStatusBar.vue';
import { bs } from '@/i18n/bs';

const props = defineProps<{ blocked: boolean; progress: boolean[] }>();

const t = bs.demo;
const progress = ref<boolean[]>(props.progress);
const frameKey = ref(0);
const sheetOpen = ref(false);
const clock = ref('');
const narrow = ref(false);
let narrowQuery: MediaQueryList | undefined;
const onNarrow = () => (narrow.value = narrowQuery?.matches ?? false);

watch(
    () => props.progress,
    (value) => (progress.value = value),
);

const current = computed(() => progress.value.indexOf(false));

async function refreshProgress(): Promise<void> {
    const response = await fetch('/demo/napredak', {
        headers: { Accept: 'application/json' },
    });

    if (response.ok) {
        progress.value = (
            (await response.json()) as { progress: boolean[] }
        ).progress;
    }
}

function onMessage(event: MessageEvent): void {
    if (
        event.origin === window.location.origin &&
        event.data?.type === 'rk:navigated'
    ) {
        void refreshProgress();
    }
}

function reset(): void {
    sheetOpen.value = false;
    router.post('/demo/reset', {}, { onSuccess: () => frameKey.value++ });
}

let timer: ReturnType<typeof setInterval> | undefined;

function tick(): void {
    clock.value = new Intl.DateTimeFormat('bs-BA', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'Europe/Sarajevo',
    }).format(new Date());
}

onMounted(() => {
    window.addEventListener('message', onMessage);
    narrowQuery = window.matchMedia('(max-width: 639px)');
    onNarrow();
    narrowQuery.addEventListener('change', onNarrow);
    tick();
    timer = setInterval(tick, 15_000);
});

onBeforeUnmount(() => {
    window.removeEventListener('message', onMessage);
    narrowQuery?.removeEventListener('change', onNarrow);
    clearInterval(timer);
});
</script>

<template>
    <Head :title="t.title">
        <meta name="robots" content="noindex" />
    </Head>

    <div class="min-h-screen bg-app text-[16px] leading-[1.5] text-ink">
        <header
            class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-b border-line bg-white px-[clamp(20px,4vw,56px)] py-3.5"
        >
            <div class="flex flex-wrap items-baseline gap-3">
                <a
                    href="/"
                    class="flex min-h-11 items-center"
                    :aria-label="bs.appName"
                    ><Logo :height="30"
                /></a>
                <span
                    class="rounded-chip border border-dashed border-proposed bg-proposed-bg px-[7px] py-0.5 font-mono text-[12px] text-proposed-text"
                    >{{ t.badge }}</span
                >
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    href="/"
                    anchor
                    variant="subtle"
                    size="sm"
                    weight="medium"
                    class="px-4"
                    >{{ t.back }}</Button
                >
                <Button
                    v-if="!blocked"
                    variant="outline"
                    size="sm"
                    class="px-4"
                    @click="reset"
                    >{{ t.reset }}</Button
                >
            </div>
        </header>

        <main
            class="flex flex-wrap items-start justify-center gap-x-[clamp(32px,6vw,96px)] gap-y-10 px-[clamp(20px,4vw,56px)] py-[clamp(24px,4vw,56px)]"
        >
            <div
                v-if="blocked"
                class="max-w-[520px] rounded-card border border-line bg-white px-4.5 py-4"
            >
                <p class="m-0 text-[15px] leading-[1.5]">{{ t.blocked }}</p>
                <Button href="/danas" class="mt-3">{{ t.openApp }}</Button>
            </div>

            <template v-else>
                <!-- On phones the app fills the width; a phone-in-a-phone is too small to use. -->
                <component
                    :is="narrow ? 'div' : PhoneFrame"
                    v-bind="
                        narrow
                            ? {
                                  class: 'relative order-1 flex h-[calc(100svh-150px)] min-h-[560px] w-full flex-col overflow-hidden rounded-card border border-line bg-app',
                              }
                            : { fit: 'demo', class: 'order-1' }
                    "
                >
                    <PhoneStatusBar
                        :clock="narrow ? '' : clock"
                        :tone="narrow ? 'surface' : 'app'"
                    >
                        <button
                            type="button"
                            class="flex min-h-11 items-center border-0 bg-transparent p-0"
                            @click="sheetOpen = true"
                        >
                            <span
                                class="rounded-full border border-dashed border-proposed bg-proposed-bg px-[11px] py-1 text-label font-semibold text-proposed-text"
                                >{{ t.pill }}</span
                            >
                        </button>
                    </PhoneStatusBar>
                    <iframe
                        :key="frameKey"
                        src="/danas"
                        :title="t.phoneLabel"
                        class="min-h-0 w-full flex-1 border-0 bg-app"
                    />
                    <BottomSheet
                        v-model:open="sheetOpen"
                        :title="t.sheetTitle"
                        contained
                    >
                        <p
                            class="mt-2 mb-3.5 text-[14.5px] leading-[1.45] text-body"
                        >
                            {{ t.sheetText }}
                        </p>
                        <Button block @click="reset">{{ t.reset }}</Button>
                        <Button
                            block
                            variant="ghost"
                            size="md"
                            weight="medium"
                            class="mt-1.5"
                            @click="sheetOpen = false"
                            >{{ bs.common.close }}</Button
                        >
                    </BottomSheet>
                </component>

                <section class="order-2 max-w-[460px] flex-[1_1_320px]">
                    <div
                        class="font-mono text-[13px] tracking-kicker text-muted uppercase"
                    >
                        {{ t.guideKicker }}
                    </div>
                    <h1
                        class="mt-2.5 mb-[18px] font-serif text-[34px] leading-[1.1] font-medium tracking-[-.01em]"
                    >
                        {{ t.guideTitle }}
                    </h1>
                    <ol class="m-0 list-none border-t-2 border-ink p-0">
                        <li
                            v-for="(step, i) in t.steps"
                            :key="step[0]"
                            class="grid grid-cols-[30px_minmax(0,1fr)] gap-3 border-b border-line px-2.5 py-[11px]"
                            :class="
                                i === current ? 'bg-white' : 'bg-transparent'
                            "
                            :aria-current="i === current ? 'step' : undefined"
                        >
                            <span
                                class="pt-0.5 font-mono text-[13px] font-medium"
                                :class="
                                    progress[i]
                                        ? 'text-confirmed'
                                        : i === current
                                          ? 'text-proposed-text'
                                          : 'text-muted'
                                "
                                >{{
                                    progress[i]
                                        ? '✓'
                                        : String(i + 1).padStart(2, '0')
                                }}</span
                            >
                            <span>
                                <span
                                    class="block text-card"
                                    :class="[
                                        i === current
                                            ? 'font-semibold'
                                            : 'font-normal',
                                        progress[i] ? 'text-muted' : 'text-ink',
                                    ]"
                                    >{{ step[0] }}</span
                                >
                                <span
                                    v-if="i === current"
                                    class="mt-0.5 block text-[14px] text-body"
                                    >{{ step[1] }}</span
                                >
                            </span>
                        </li>
                    </ol>
                    <p class="mt-3.5 mb-0 text-note text-muted">
                        {{ t.guideNote }}
                    </p>
                </section>
            </template>
        </main>
    </div>
</template>
