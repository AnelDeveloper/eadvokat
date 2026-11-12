/**
 * Bosnian display formats: dates as dd.mm.gggg., money as 1.234,56 KM.
 * Dates travel between server and client as ISO strings (YYYY-MM-DD).
 */

export const WEEKDAYS = [
    'nedjelja',
    'ponedjeljak',
    'utorak',
    'srijeda',
    'četvrtak',
    'petak',
    'subota',
] as const;

export const WEEKDAYS_SHORT = [
    'ned',
    'pon',
    'uto',
    'sri',
    'čet',
    'pet',
    'sub',
] as const;

export const MONTHS = [
    'januar',
    'februar',
    'mart',
    'april',
    'maj',
    'juni',
    'juli',
    'august',
    'septembar',
    'oktobar',
    'novembar',
    'decembar',
] as const;

const pad = (n: number): string => String(n).padStart(2, '0');

/** Parses an ISO date (YYYY-MM-DD) as a local calendar date. */
export function fromIso(iso: string): Date {
    const [y, m, d] = iso.slice(0, 10).split('-').map(Number);

    return new Date(y, m - 1, d);
}

export function toIso(date: Date): string {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

export function formatDate(iso: string | null | undefined): string {
    if (!iso) {
        return '';
    }

    const d = fromIso(iso);

    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}.`;
}

export function formatDayMonth(iso: string): string {
    const d = fromIso(iso);

    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.`;
}

export function weekday(iso: string): string {
    return WEEKDAYS[fromIso(iso).getDay()];
}

/** "srijeda, 11. novembar 2026." */
export function formatLongDate(iso: string): string {
    const d = fromIso(iso);

    return `${weekday(iso)}, ${d.getDate()}. ${MONTHS[d.getMonth()]} ${d.getFullYear()}.`;
}

/** Parses "dd.mm.gggg." (trailing dot optional) into ISO, or null. */
export function parseDate(input: string): string | null {
    const m = /^\s*(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})\.?\s*$/.exec(input);

    if (!m) {
        return null;
    }

    const [day, month, year] = [Number(m[1]), Number(m[2]), Number(m[3])];
    const d = new Date(year, month - 1, day);

    if (
        d.getFullYear() !== year ||
        d.getMonth() !== month - 1 ||
        d.getDate() !== day
    ) {
        return null;
    }

    return toIso(d);
}

export function addDays(iso: string, days: number): string {
    const d = fromIso(iso);
    d.setDate(d.getDate() + days);

    return toIso(d);
}

/** Whole calendar days from `fromIso` to `toIsoDate`. */
export function daysBetween(from: string, to: string): number {
    return Math.round(
        (fromIso(to).getTime() - fromIso(from).getTime()) / 86_400_000,
    );
}

export function formatMoney(
    amount: number | string | null | undefined,
): string {
    if (amount === null || amount === undefined || amount === '') {
        return '';
    }

    const [int, dec] = Number(amount).toFixed(2).split('.');

    return `${int.replace(/\B(?=(\d{3})+(?!\d))/g, '.')},${dec}`;
}

/** Parses "1.234,56" (or "1234.56") into a decimal string "1234.56", or null. */
export function parseMoney(input: string): string | null {
    const s = input.trim().replace(/\s|KM/g, '');

    if (s === '') {
        return null;
    }

    const normalized = s.includes(',')
        ? s.replace(/\./g, '').replace(',', '.')
        : s;

    if (!/^\d+(\.\d{1,2})?$/.test(normalized)) {
        return null;
    }

    return Number(normalized).toFixed(2);
}
