import { describe, expect, it } from 'vite-plus/test';
import {
    addDays,
    daysBetween,
    formatDate,
    formatLongDate,
    formatMoney,
    parseDate,
    parseMoney,
    weekday,
} from './format';

describe('dates', () => {
    it('formats as dd.mm.gggg. with a trailing dot', () => {
        expect(formatDate('2026-11-26')).toBe('26.11.2026.');
        expect(formatDate(null)).toBe('');
    });

    it('formats the long Danas date', () => {
        expect(formatLongDate('2026-11-11')).toBe(
            'srijeda, 11. novembar 2026.',
        );
        expect(weekday('2026-11-26')).toBe('četvrtak');
    });

    it('parses user input and rejects impossible dates', () => {
        expect(parseDate('11.11.2026.')).toBe('2026-11-11');
        expect(parseDate('1.2.2026')).toBe('2026-02-01');
        expect(parseDate('31.02.2026.')).toBeNull();
        expect(parseDate('nije datum')).toBeNull();
    });

    it('adds calendar days without skipping anything', () => {
        expect(addDays('2026-11-11', 15)).toBe('2026-11-26');
        expect(addDays('2026-12-25', 10)).toBe('2027-01-04');
        expect(daysBetween('2026-11-11', '2026-11-26')).toBe(15);
    });
});

describe('money', () => {
    it('formats as 1.234,56', () => {
        expect(formatMoney(1234.56)).toBe('1.234,56');
        expect(formatMoney('480.00')).toBe('480,00');
        expect(formatMoney(null)).toBe('');
    });

    it('parses Bosnian and plain decimal input', () => {
        expect(parseMoney('1.234,56')).toBe('1234.56');
        expect(parseMoney('480,00 KM')).toBe('480.00');
        expect(parseMoney('120')).toBe('120.00');
        expect(parseMoney('abc')).toBeNull();
        expect(parseMoney('')).toBeNull();
    });
});
