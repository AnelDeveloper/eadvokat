import { ref } from 'vue';

const TOAST_MS = 3400;

// Client-only state: toasts are raised by user actions or flash events, never during SSR.
const message = ref('');
let timer: ReturnType<typeof setTimeout> | undefined;

export function showToast(text: string): void {
    clearTimeout(timer);
    message.value = text;
    timer = setTimeout(() => (message.value = ''), TOAST_MS);
}

export function useToast() {
    return { message, show: showToast };
}
