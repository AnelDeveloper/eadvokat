<template>
    <!-- Sits above the tab bar on phones via --rk-bar-offset (set by the app layout). -->
    <div
        class="sticky bottom-[var(--rk-bar-offset,0px)] z-10 flex-none border-t border-line bg-white px-3.5 pt-2.5 pb-3 lg:bg-app/95 lg:px-8 lg:backdrop-blur"
    >
        <slot />
    </div>
</template>
