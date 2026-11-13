<template>
    <!-- Sits above the tab bar on phones via --rk-bar-offset (set by the app layout). -->
    <div
        class="sticky bottom-[var(--rk-bar-offset,0px)] z-10 flex-none border-t border-line bg-white px-3.5 pt-2.5 pb-3 shadow-bar lg:border-t-0 lg:bg-app/90 lg:px-8 lg:shadow-none lg:backdrop-blur"
    >
        <slot />
    </div>
</template>
