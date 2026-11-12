import { createInertiaApp, router } from '@inertiajs/vue3';
import { showToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import FlowLayout from '@/layouts/FlowLayout.vue';

void createInertiaApp({
    title: (title) => (title ? `${title} · Rokovnik` : 'Rokovnik'),
    layout: (name) => {
        switch (true) {
            case name === 'Landing':
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
    router.on('flash', (event) => {
        const toast = event.detail.flash.toast;

        if (typeof toast === 'string' && toast !== '') {
            showToast(toast);
        }
    });
}
