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

export function useTranslation(): { t: TranslateFn } {
    const { translations } = usePage().props;

    const t = useCallback<TranslateFn>(
        (key, replacements) => translate(translations, key, replacements),
        [translations],
    );

    return { t };
}
