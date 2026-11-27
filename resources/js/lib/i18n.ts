import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import type { Translations } from '@/types/i18n';

export type Replacements = Record<string, string | number>;
export type TranslateFn = (key: string, replacements?: Replacements) => string;

function lookup(translations: Translations, key: string): string | undefined {
    let node: string | Translations | undefined = translations;

    for (const segment of key.split('.')) {
        if (node === undefined || typeof node === 'string') {
            return undefined;
        }

        node = node[segment];
    }

    return typeof node === 'string' ? node : undefined;
}

/**
 * Same semantics as Laravel's __(): `:name` placeholders are replaced and a
 * missing key falls back to the key itself, so gaps are visible, not blank.
 */
export function translate(
    translations: Translations,
    key: string,
    replacements: Replacements = {},
): string {
    const line = lookup(translations, key) ?? key;

    return Object.entries(replacements).reduce(
        (text, [name, value]) => text.replaceAll(`:${name}`, String(value)),
        line,
    );
}

export type ChoiceFn = (
    key: string,
    count: number,
    replacements?: Replacements,
) => string;

/**
 * Picks the segment of a Laravel trans_choice() line for `count`:
 * "{0} none|{1} one|[2,*] :count many", or plain "one|many".
 */
function chooseSegment(line: string, count: number): string {
    const segments = line.split('|');

    for (const segment of segments) {
        const exact = segment.match(/^\s*\{(\d+)\}\s?(.*)$/s);

        if (exact !== null && Number(exact[1]) === count) {
            return exact[2];
        }

        const range = segment.match(/^\s*\[(\d+),(\d+|\*)\]\s?(.*)$/s);

        if (
            range !== null &&
            count >= Number(range[1]) &&
            (range[2] === '*' || count <= Number(range[2]))
        ) {
            return range[3];
        }
    }

    const plain = segments.filter((segment) => !/^\s*[{[]/.test(segment));

    return (count === 1 ? plain[0] : (plain[1] ?? plain[0])) ?? line;
}

export function translateChoice(
    translations: Translations,
    key: string,
    count: number,
    replacements: Replacements = {},
): string {
    const line = lookup(translations, key) ?? key;

    return translate({ line: chooseSegment(line, count) }, 'line', {
        count,
        ...replacements,
    });
}

export function useTranslation(): { t: TranslateFn; tc: ChoiceFn } {
    const { translations } = usePage().props;

    const t = useCallback<TranslateFn>(
        (key, replacements) => translate(translations, key, replacements),
        [translations],
    );

    const tc = useCallback<ChoiceFn>(
        (key, count, replacements) =>
            translateChoice(translations, key, count, replacements),
        [translations],
    );

    return { t, tc };
}
