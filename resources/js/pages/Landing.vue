<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BriefingCard from '@/components/today/BriefingCard.vue';
import QuickActions from '@/components/today/QuickActions.vue';
import TodayHeader from '@/components/today/TodayHeader.vue';
import Button from '@/components/ui/Button.vue';
import PhoneFrame from '@/components/ui/PhoneFrame.vue';
import PhoneStatusBar from '@/components/ui/PhoneStatusBar.vue';
import StatusChip from '@/components/ui/StatusChip.vue';
import TabBar from '@/components/ui/TabBar.vue';
import { landingDemo } from '@/data/landingDemo';
import { bs } from '@/i18n/bs';
import { login, today } from '@/routes';

const t = bs.landing;
const page = usePage();
const signedIn = computed(() => page.props.auth.user !== null);

// The landing page can be exported as a static site (Vercel); then the app
// links point to the app's own origin.
const appOrigin = (import.meta.env.VITE_APP_ORIGIN as string | undefined) ?? '';
const prototypeHref = `${appOrigin}/demo`;

const tabs = [
    { key: 'today', label: bs.tabs.today, href: '', active: true },
    { key: 'matters', label: bs.tabs.matters, href: '', active: false },
    { key: 'calendar', label: bs.tabs.calendar, href: '', active: false },
    { key: 'mail', label: bs.tabs.mail, href: '', active: false },
];

const rowTone = {
    recorded: 'text-muted',
    proposed: 'text-proposed-text',
    confirmed: 'text-confirmed-text',
} as const;

const gutter = 'px-[clamp(20px,4vw,56px)]';
const sectionY = 'py-[clamp(40px,6vw,88px)]';
const kicker = 'font-mono text-[13px] tracking-kicker uppercase text-muted';
const h2 =
    'm-0 font-serif text-section font-medium tracking-[-.015em] text-balance';
</script>

<template>
    <Head :title="t.metaTitle">
        <meta name="description" :content="t.metaDescription" />
        <meta property="og:title" :content="`${bs.appName} · ${t.metaTitle}`" />
        <meta property="og:description" :content="t.metaDescription" />
        <meta property="og:type" content="website" />
    </Head>

    <div class="min-h-screen bg-app text-[16px] leading-[1.5] text-ink">
        <header class="border-b border-line bg-white py-3" :class="gutter">
            <div class="flex items-center justify-between gap-x-6 gap-y-2">
                <span
                    class="font-serif text-[24px] font-semibold tracking-[-.01em]"
                    >{{ bs.appName }}</span
                >
                <div class="flex items-center gap-x-[22px] text-[15px]">
                    <nav
                        class="hidden items-center gap-x-[22px] md:flex"
                        :aria-label="t.nav.sections"
                    >
                        <a href="#tok" class="no-underline">{{ t.nav.flow }}</a>
                        <a href="#nadzor" class="no-underline">{{
                            t.nav.oversight
                        }}</a>
                        <a href="#pilot" class="no-underline">{{
                            t.nav.pilot
                        }}</a>
                    </nav>
                    <a
                        v-if="appOrigin"
                        :href="`${appOrigin}/login`"
                        class="inline-flex min-h-11 items-center no-underline"
                        >{{ t.nav.login }}</a
                    >
                    <Link
                        v-else
                        :href="signedIn ? today() : login()"
                        class="inline-flex min-h-11 items-center no-underline"
                        >{{ signedIn ? t.nav.openApp : t.nav.login }}</Link
                    >
                    <span class="hidden sm:block">
                        <Button :href="prototypeHref" anchor size="nav">{{
                            t.nav.cta
                        }}</Button>
                    </span>
                </div>
            </div>
            <nav
                class="-mb-1 flex gap-x-6 text-[15px] md:hidden"
                :aria-label="t.nav.sections"
            >
                <a
                    href="#tok"
                    class="inline-flex min-h-11 items-center no-underline"
                    >{{ t.nav.flow }}</a
                >
                <a
                    href="#nadzor"
                    class="inline-flex min-h-11 items-center no-underline"
                    >{{ t.nav.oversight }}</a
                >
                <a
                    href="#pilot"
                    class="inline-flex min-h-11 items-center no-underline"
                    >{{ t.nav.pilot }}</a
                >
            </nav>
        </header>

        <main
            class="flex flex-wrap items-start justify-center gap-x-[clamp(32px,6vw,96px)] gap-y-10 py-[clamp(24px,4vw,56px)]"
            :class="gutter"
        >
            <div
                class="order-1 max-w-[660px] flex-[1_1_420px] pt-[clamp(0px,3vw,48px)]"
            >
                <div :class="kicker">{{ t.hero.kicker }}</div>
                <h1
                    class="mt-4 mb-0 font-serif text-display font-medium tracking-[-.02em] text-balance"
                >
                    {{ t.hero.title }}
                </h1>
                <p
                    class="mt-6 mb-0 max-w-[560px] text-lead text-pretty text-body"
                >
                    {{ t.hero.lead }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3.5">
                    <Button :href="prototypeHref" anchor size="hero">{{
                        t.hero.cta
                    }}</Button>
                    <span class="max-w-[260px] text-[14px] text-muted">
                        <span class="min-[900px]:hidden">{{
                            t.hero.ctaNoteMobile
                        }}</span>
                        <span class="hidden min-[900px]:inline">{{
                            t.hero.ctaNote
                        }}</span>
                    </span>
                </div>
                <div class="mt-11 max-w-[560px] border-t-2 border-ink">
                    <div
                        class="flex justify-between gap-3 border-b border-line py-2 font-mono text-sub text-muted"
                    >
                        <span>{{ t.hero.tableCase }}</span
                        ><span>{{ t.hero.tableFictional }}</span>
                    </div>
                    <div
                        v-for="row in t.hero.rows"
                        :key="row.time"
                        class="grid grid-cols-[54px_minmax(0,1fr)_auto] items-baseline gap-3 border-b border-line py-2.5 text-[15px]"
                    >
                        <span class="font-mono text-[13px]">{{
                            row.time
                        }}</span>
                        <span>{{ row.text }}</span>
                        <span
                            class="text-[12px] font-semibold"
                            :class="rowTone[row.tone]"
                            >{{ row.tag }}</span
                        >
                    </div>
                </div>
            </div>

            <a
                :href="prototypeHref"
                class="order-2 block flex-none text-ink no-underline hover:text-ink"
                :aria-label="t.phoneLabel"
            >
                <PhoneFrame aria-hidden="true">
                    <PhoneStatusBar :clock="landingDemo.clock" />
                    <div
                        class="min-h-0 flex-1 overflow-hidden px-4.5 pt-1.5 pb-4.5"
                    >
                        <TodayHeader :date="landingDemo.date" as="div" />
                        <BriefingCard
                            class="mt-3.5"
                            :lawyer="landingDemo.lawyer"
                            :groups="landingDemo.groups"
                            :interactive="false"
                        />
                    </div>
                    <QuickActions :interactive="false" />
                    <TabBar :items="tabs" :interactive="false" />
                </PhoneFrame>
            </a>
        </main>

        <section
            id="tok"
            class="scroll-mt-2 border-t border-line bg-white"
            :class="[gutter, sectionY]"
        >
            <div class="mx-auto max-w-[1280px]">
                <div :class="kicker">{{ t.flow.kicker }}</div>
                <h2 class="mt-2.5 max-w-[820px]" :class="h2">
                    {{ t.flow.title }}
                </h2>
                <div
                    class="mt-9 grid grid-cols-[repeat(auto-fit,minmax(190px,1fr))] border-t-2 border-ink"
                >
                    <div
                        v-for="(step, i) in t.flow.steps"
                        :key="step.title"
                        class="border-b border-line pt-4 pb-[22px]"
                        :class="
                            i === t.flow.steps.length - 1 ? 'pr-0' : 'pr-[18px]'
                        "
                    >
                        <div class="font-mono text-[13px] text-muted">
                            {{ step.meta }}
                        </div>
                        <div class="mt-2.5 text-[20px] font-semibold">
                            {{ step.title }}
                        </div>
                        <p
                            class="mt-1.5 mb-3 text-[15px] leading-[1.45] text-body"
                        >
                            <span
                                v-if="'quote' in step"
                                class="font-serif italic"
                                >{{ step.quote }}</span
                            >
                            {{ step.text }}
                        </p>
                        <template
                            v-for="(chip, c) in step.chips"
                            :key="chip.label"
                        >
                            <StatusChip
                                :status="chip.status"
                                :label="chip.label"
                                size="md"
                            />{{ c < step.chips.length - 1 ? ' ' : '' }}
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <section :class="[gutter, sectionY]">
            <div
                class="mx-auto flex max-w-[1280px] flex-wrap gap-x-[clamp(32px,6vw,96px)] gap-y-7"
            >
                <h2 class="flex-[1_1_300px]" :class="h2">
                    {{ t.benefits.title }}
                </h2>
                <div class="flex-[2_1_460px] border-t-2 border-ink">
                    <div
                        v-for="item in t.benefits.items"
                        :key="item.title"
                        class="grid gap-x-7 gap-y-2 border-b border-line py-[18px] sm:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]"
                    >
                        <div class="text-[20px] font-semibold">
                            {{ item.title }}
                        </div>
                        <p class="m-0 text-[17px] leading-[1.5] text-body">
                            {{ item.text }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            id="nadzor"
            class="scroll-mt-2 bg-ink text-white"
            :class="[gutter, sectionY]"
        >
            <div class="mx-auto max-w-[1280px]">
                <div
                    class="font-mono text-[13px] tracking-kicker text-line-strong uppercase"
                >
                    {{ t.oversight.kicker }}
                </div>
                <h2 class="mt-2.5 max-w-[860px]" :class="h2">
                    {{ t.oversight.title }}
                </h2>
                <p
                    class="mt-4 mb-0 max-w-[760px] text-[18px] leading-[1.5] text-line"
                >
                    {{ t.oversight.lead }}
                </p>
                <div class="mt-[30px] border-t-2 border-white">
                    <div
                        v-for="item in t.oversight.items"
                        :key="item.question"
                        class="grid gap-x-8 gap-y-2 border-b border-muted py-[18px] md:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]"
                    >
                        <div class="text-[20px] font-semibold">
                            {{ item.question }}
                        </div>
                        <p class="m-0 text-[16px] leading-[1.5] text-line">
                            {{ item.answer }}
                        </p>
                    </div>
                </div>
                <p
                    class="mt-[22px] mb-0 max-w-[760px] text-[17px] leading-[1.5] text-white"
                >
                    {{ t.oversight.closing }}
                </p>
            </div>
        </section>

        <section
            id="pilot"
            class="scroll-mt-2 bg-white"
            :class="[gutter, sectionY]"
        >
            <div
                class="mx-auto flex max-w-[1280px] flex-wrap gap-x-[clamp(32px,6vw,96px)] gap-y-7"
            >
                <div class="flex-[1_1_320px]">
                    <div :class="kicker">{{ t.pilot.kicker }}</div>
                    <h2 class="mt-2.5" :class="h2">{{ t.pilot.title }}</h2>
                    <p class="mt-4 mb-0 text-[17px] leading-[1.5] text-body">
                        {{ t.pilot.lead }}
                    </p>
                </div>
                <div class="flex-[2_1_460px] border-t-2 border-ink">
                    <div
                        v-for="week in t.pilot.weeks"
                        :key="week.when"
                        class="grid gap-x-6 gap-y-1 border-b border-line py-4 sm:grid-cols-[130px_minmax(0,1fr)]"
                    >
                        <div class="font-mono text-[14px]">{{ week.when }}</div>
                        <p class="m-0 text-[17px] leading-[1.5]">
                            {{ week.text }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="border-t border-line py-[clamp(32px,4vw,56px)]"
            :class="gutter"
        >
            <div
                class="mx-auto flex max-w-[1280px] flex-wrap items-baseline gap-x-[clamp(32px,6vw,96px)] gap-y-3"
            >
                <h2
                    class="m-0 flex-[1_1_220px] font-serif text-[28px] leading-[1.15] font-medium"
                >
                    {{ t.later.title }}
                </h2>
                <p
                    class="m-0 flex-[3_1_460px] text-[17px] leading-[1.6] text-body"
                >
                    {{ t.later.text }}
                </p>
            </div>
        </section>

        <footer
            class="border-t border-line bg-white pt-[26px] pb-10"
            :class="gutter"
        >
            <div
                class="mx-auto flex max-w-[1280px] flex-wrap items-center justify-between gap-x-8 gap-y-4"
            >
                <p
                    class="m-0 max-w-[760px] text-[14px] leading-[1.5] text-muted"
                >
                    {{ t.footer.text }}
                </p>
                <Button :href="prototypeHref" anchor size="cta">{{
                    t.footer.cta
                }}</Button>
            </div>
        </footer>
    </div>
</template>
