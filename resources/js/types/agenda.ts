import type { DisplayStatus } from './status';

/** A timeline / calendar row as produced by App\Queries\AgendaItem::toRow(). */
export type AgendaRow = {
    id: string;
    when: string;
    time: string | null;
    type: string | null;
    title: string;
    sub: string;
    status: DisplayStatus;
    href: string | null;
    documentId: number | null;
    toggleUrl: string | null;
};

export type MatterHeader = {
    id: number;
    caseNumber: string;
    title: string;
    meta: string;
    costLine: string;
};
