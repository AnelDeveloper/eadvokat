import type { DisplayStatus } from './status';

export type BriefingRow = {
    id: string;
    when: string;
    title: string;
    sub?: string;
    status: DisplayStatus;
    label?: string;
    href?: string;
    /** Reminder rows toggle "checked" instead of navigating. */
    toggleUrl?: string;
};

export type BriefingGroup = {
    key: 'hearings' | 'deadlines' | 'court' | 'review' | 'tomorrow';
    rows: BriefingRow[];
};
