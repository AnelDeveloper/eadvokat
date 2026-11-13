import { createInertiaApp, router } from '@inertiajs/vue3';
import { showToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import FlowLayout from '@/layouts/FlowLayout.vue';

void createInertiaApp({
    title: (title) => (title ? `${title} · Ročište` : 'Ročište'),
    layout: (name) => {
        switch (true) {
            case name === 'Landing' || name === 'Demo':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('flow/'):
                return FlowLayout;
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: 'var(--color-ink)',
    },
});

if (typeof window !== 'undefined') {
    // Inside the /demo phone frame: tell the guide that the app moved on.
    if (window.parent !== window) {
        router.on('success', () =>
            window.parent.postMessage(
                { type: 'rk:navigated' },
                window.location.origin,
            ),
        );
    }

    // Navigation links prefetch on hover. Any change (POST/PUT/DELETE) drops
    // those cached pages so deadlines and statuses are never shown stale.
    router.on('before', (event) => {
        if (event.detail.visit.method !== 'get') {
            router.flushAll();
        }
    });

    router.on('flash', (event) => {
        const toast = event.detail.flash.toast;

        if (typeof toast === 'string' && toast !== '') {
            showToast(toast);
        }
    });
}
