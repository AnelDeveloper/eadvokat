<script setup lang="ts">
/**
 * A 390×844 phone (11px bezel) scaled to fit the viewport height.
 * The prototype computes the scale in JS from window.innerHeight; here CSS
 * height breakpoints approximate clamp((100vh - 104px) / 866, 0.6, max) so
 * server-rendered and hydrated markup are identical and nothing shifts.
 */
const { fit = 'landing' } = defineProps<{ fit?: 'landing' | 'demo' }>();
</script>

<template>
    <div class="phone-fit relative flex-none" :class="`phone-fit--${fit}`">
        <div
            class="phone absolute top-0 left-0 box-content flex h-[844px] w-[390px] origin-top-left flex-col overflow-hidden rounded-phone border-[11px] border-ink-deep bg-app font-sans text-[15px] leading-[1.4] text-ink shadow-phone"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
/* Each step uses the scale at the lower edge of its height range, so the
   frame never exceeds clamp((100vh - 104px) / 866, 0.6, max). */
.phone-fit {
    --s: 0.92;
    width: calc(412px * var(--s));
    height: calc(866px * var(--s));
}

.phone {
    transform: scale(var(--s));
}

.phone-fit--demo {
    --s: 1.2;
}

@media (max-height: 1142px) {
    .phone-fit--demo {
        --s: 1.1;
    }
}

@media (max-height: 1055px) {
    .phone-fit--demo {
        --s: 1;
    }
}

@media (max-height: 969px) {
    .phone-fit--demo {
        --s: 0.92;
    }
}

@media (max-height: 900px) {
    .phone-fit--landing,
    .phone-fit--demo {
        --s: 0.85;
    }
}

@media (max-height: 839px) {
    .phone-fit--landing,
    .phone-fit--demo {
        --s: 0.8;
    }
}

@media (max-height: 796px) {
    .phone-fit--landing,
    .phone-fit--demo {
        --s: 0.75;
    }
}

@media (max-height: 752px) {
    .phone-fit--landing,
    .phone-fit--demo {
        --s: 0.7;
    }
}

@media (max-height: 709px) {
    .phone-fit--landing,
    .phone-fit--demo {
        --s: 0.65;
    }
}

@media (max-height: 666px) {
    .phone-fit--landing,
    .phone-fit--demo {
        --s: 0.6;
    }
}

/* Narrow phones: keep the frame inside the 20px page gutters. */
@media (max-width: 430px) and (min-height: 841px) {
    .phone-fit--landing {
        --s: 0.85;
    }
}
</style>
