<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ScreenBar from '@/components/ui/ScreenBar.vue';
import { bs } from '@/i18n/bs';

defineProps<{
    document: {
        id: number;
        name: string;
        paragraphs: Array<{ text: string; align?: string; bold?: boolean }>;
        isFictional: boolean;
        fileUrl: string | null;
        isImage: boolean;
    };
    backUrl: string;
}>();
</script>

<template>
    <Head :title="document.name" />
    <div class="flex min-h-svh flex-col bg-viewer">
        <ScreenBar :title="document.name" :back-url="backUrl" />
        <div class="flex-1 p-4.5">
            <article
                class="min-h-[440px] bg-white px-[22px] pt-[26px] pb-[30px] font-serif text-[14.5px] leading-[1.5] shadow-page"
            >
                <p
                    v-for="(p, i) in document.paragraphs"
                    :key="i"
                    class="mt-0 mb-[9px]"
                    :class="[
                        p.align === 'center'
                            ? 'text-center'
                            : p.align === 'right'
                              ? 'text-right'
                              : 'text-left',
                        p.bold ? 'font-semibold' : 'font-normal',
                    ]"
                >
                    {{ p.text }}
                </p>
                <div
                    v-if="document.isFictional"
                    class="mt-[26px] font-mono text-[11px] text-proposed-text"
                >
                    {{ bs.document.fictional }}
                </div>
            </article>
            <a
                v-if="document.fileUrl"
                :href="document.fileUrl"
                target="_blank"
                rel="noopener"
                class="mt-3 flex min-h-11 items-center text-[14px] font-semibold text-ink underline"
                >{{ bs.document.openFile }}</a
            >
            <img
                v-if="document.fileUrl && document.isImage"
                :src="document.fileUrl"
                :alt="document.name"
                class="mt-2 block w-full rounded-chip shadow-page"
            />
        </div>
    </div>
</template>
