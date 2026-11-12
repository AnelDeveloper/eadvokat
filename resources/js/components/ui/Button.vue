<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type Variant =
    | 'primary'
    | 'outline'
    | 'subtle'
    | 'ghost'
    | 'confirm'
    | 'inactive';
type Size = 'sm' | 'md' | 'lg' | 'xl' | 'hero';

const {
    variant = 'primary',
    size = 'lg',
    block = false,
    href,
    anchor = false,
    type = 'button',
    disabled = false,
    weight = 'semibold',
} = defineProps<{
    variant?: Variant;
    size?: Size;
    block?: boolean;
    /** Inertia link target; with `anchor` a plain <a> (hash links, downloads). */
    href?: string;
    anchor?: boolean;
    type?: 'button' | 'submit';
    disabled?: boolean;
    weight?: 'medium' | 'semibold';
}>();

const variants: Record<Variant, string> = {
    primary: 'border-0 bg-ink text-white hover:bg-ink-deep hover:text-white',
    outline: 'border border-ink bg-white text-ink hover:text-ink',
    subtle: 'border border-line-strong bg-white text-ink hover:text-ink',
    ghost: 'border-0 bg-transparent text-ink hover:text-ink',
    confirm: 'border-0 bg-confirmed text-white hover:text-white',
    // Looks disabled but stays clickable so it can explain what is missing.
    inactive: 'border-0 bg-faint text-white hover:text-white',
};

const sizes: Record<Size, string> = {
    sm: 'min-h-11 rounded-field px-3 text-[13.5px]',
    md: 'min-h-12 rounded-card px-3 text-[14.5px]',
    lg: 'min-h-13 rounded-card px-3 text-action',
    xl: 'min-h-14 rounded-card px-3 text-row',
    hero: 'min-h-15 rounded-card px-[30px] text-[19px]',
};

const classes = computed(() => [
    'inline-flex items-center justify-center gap-2 text-center no-underline transition-colors disabled:cursor-not-allowed disabled:opacity-60',
    variants[variant],
    sizes[size],
    weight === 'medium' ? 'font-medium' : 'font-semibold',
    block && 'w-full',
]);
</script>

<template>
    <a v-if="href && anchor" :href="href" :class="classes"><slot /></a>
    <Link v-else-if="href" :href="href" :class="classes"><slot /></Link>
    <button v-else :type="type" :disabled="disabled" :class="classes">
        <slot />
    </button>
</template>
