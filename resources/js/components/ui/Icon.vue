<script setup lang="ts">
/**
 * Line icons drawn on a 24px grid (stroke = currentColor), so they inherit
 * the text colour of whatever holds them. No icon package needed.
 */
export type IconName =
    | 'today'
    | 'matters'
    | 'calendar'
    | 'mail'
    | 'settings'
    | 'upload'
    | 'camera'
    | 'mic'
    | 'send'
    | 'plus'
    | 'logout'
    | 'chevron-right';

const { size = 20, stroke = 1.8 } = defineProps<{
    name: IconName;
    size?: number;
    stroke?: number;
}>();

const paths: Record<IconName, string[]> = {
    today: [
        'M12 3v2',
        'M12 19v2',
        'M4.6 4.6l1.4 1.4',
        'M18 18l1.4 1.4',
        'M3 12h2',
        'M19 12h2',
        'M4.6 19.4L6 18',
        'M18 6l1.4-1.4',
        'M12 8a4 4 0 1 0 0 8a4 4 0 1 0 0-8z',
    ],
    matters: [
        'M3 7.5A1.5 1.5 0 0 1 4.5 6H9l2 2h8.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5z',
    ],
    calendar: [
        'M4 6.5A1.5 1.5 0 0 1 5.5 5h13A1.5 1.5 0 0 1 20 6.5v12a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5z',
        'M4 10h16',
        'M8 3v4',
        'M16 3v4',
        'M8 14h2',
        'M14 14h2',
    ],
    mail: [
        'M3.5 6.5A1.5 1.5 0 0 1 5 5h14a1.5 1.5 0 0 1 1.5 1.5v11A1.5 1.5 0 0 1 19 19H5a1.5 1.5 0 0 1-1.5-1.5z',
        'M4 6.5l8 6l8-6',
    ],
    settings: [
        'M12 9a3 3 0 1 0 0 6a3 3 0 1 0 0-6z',
        'M19.4 13.5a7.6 7.6 0 0 0 0-3l2-1.6l-2-3.4l-2.4 1a7.6 7.6 0 0 0-2.6-1.5L14 2.5h-4l-.4 2.5A7.6 7.6 0 0 0 7 6.5l-2.4-1l-2 3.4l2 1.6a7.6 7.6 0 0 0 0 3l-2 1.6l2 3.4l2.4-1a7.6 7.6 0 0 0 2.6 1.5l.4 2.5h4l.4-2.5a7.6 7.6 0 0 0 2.6-1.5l2.4 1l2-3.4z',
    ],
    upload: [
        'M12 15V4',
        'M7.5 8.5L12 4l4.5 4.5',
        'M4 15v3.5A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5V15',
    ],
    camera: [
        'M4 8.5A1.5 1.5 0 0 1 5.5 7h2.2l1.5-2h5.6l1.5 2h2.2A1.5 1.5 0 0 1 20 8.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 17.5z',
        'M12 10a3 3 0 1 0 0 6a3 3 0 1 0 0-6z',
    ],
    mic: [
        'M12 3a3 3 0 0 0-3 3v5a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3z',
        'M5.5 11a6.5 6.5 0 0 0 13 0',
        'M12 17.5V21',
    ],
    send: ['M20.5 3.5L10 14', 'M20.5 3.5l-6.5 17l-4-6.5l-6.5-4z'],
    plus: ['M12 5v14', 'M5 12h14'],
    logout: [
        'M14 4h4.5A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5H14',
        'M10 16l-4-4l4-4',
        'M6 12h10',
    ],
    'chevron-right': ['M9.5 6l6 6l-6 6'],
};
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="stroke"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
        class="flex-none"
    >
        <path v-for="(d, i) in paths[name]" :key="i" :d="d" />
    </svg>
</template>
