import type { BriefingGroup } from '@/types';

/**
 * The static Danas screen on the landing page: an illustrative example
 * for 11.11.2026. (fictional matter 65 0 P 084512 25 P).
 */
export const landingExample = {
    clock: '08:05',
    date: 'srijeda, 11. novembar 2026.',
    lawyer: 'adv. Lejla Karić',
    groups: [
        {
            key: 'hearings',
            rows: [
                {
                    id: 'hearing',
                    when: '11:00',
                    title: 'Nastavak glavne rasprave',
                    sub: 'Sudnica 12 · Općinski sud u Sarajevu',
                    status: 'confirmed',
                },
            ],
        },
        {
            key: 'deadlines',
            rows: [
                {
                    id: 'deadline',
                    when: '13.11.',
                    title: 'Izjašnjenje na podnesak tuženog',
                    sub: 'petak · za 2 dana · 15 dana od prijema 29.10.2026.',
                    status: 'urgent',
                },
            ],
        },
        {
            key: 'court',
            rows: [
                {
                    id: 'reminder',
                    when: 'danas',
                    title: 'Provjeriti u pisarnici je li vraćena dostavnica za svjedoka',
                    sub: 'Dodirnite kada provjerite',
                    status: 'confirmed',
                },
            ],
        },
        {
            key: 'review',
            rows: [
                {
                    id: 'costs',
                    when: 'KM',
                    title: 'Troškovnik: 1 stavka čeka pregled',
                    sub: 'Nacrt u predmetu 65 0 P 084512 25 P',
                    status: 'proposed',
                },
            ],
        },
        {
            key: 'tomorrow',
            rows: [
                {
                    id: 'meeting',
                    when: '12.11.',
                    title: 'Sastanak sa strankom A. Selimović',
                    sub: 'Kancelarija',
                    status: 'confirmed',
                },
            ],
        },
    ] satisfies BriefingGroup[],
};
