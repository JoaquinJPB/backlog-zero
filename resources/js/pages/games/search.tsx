import { Head, Link, router } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect, useState } from 'react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { useTranslation } from '@/lib/i18n';
import { search, show } from '@/routes/games';
import type { GameSummary } from '@/types/games';

interface GameSearchProps {
    query: string;
    results: GameSummary[];
    failed: boolean;
    minLength: number;
}

const DEBOUNCE_MS = 300;

export default function GameSearch({
    query,
    results,
    failed,
    minLength,
}: GameSearchProps) {
    const { t } = useTranslation();
    const [term, setTerm] = useState(query);
    const [searching, setSearching] = useState(false);

    function runSearch(value: string): void {
        const trimmed = value.trim();

        router.get(search.url(), trimmed === '' ? {} : { q: trimmed }, {
            only: ['query', 'results', 'failed'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setSearching(true),
            onFinish: () => setSearching(false),
        });
    }

    useEffect(() => {
        const trimmed = term.trim();

        if (
            trimmed === query ||
            (trimmed !== '' && trimmed.length < minLength)
        ) {
            return;
        }

        const handle = setTimeout(() => runSearch(trimmed), DEBOUNCE_MS);

        return () => clearTimeout(handle);
    }, [term, query, minLength]);

    function submit(event: FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        runSearch(term);
    }

    const showEmpty =
        !failed &&
        !searching &&
        query.length >= minLength &&
        results.length === 0;

    return (
        <AppLayout>
            <Head title={t('games.search.title')} />
            <section className="mx-auto max-w-2xl">
                <h1 className="text-2xl font-semibold tracking-tight">
                    {t('games.search.title')}
                </h1>

                <form onSubmit={submit} role="search" className="mt-6">
                    <label htmlFor="game-search" className="sr-only">
                        {t('games.search.label')}
                    </label>
                    <input
                        id="game-search"
                        type="search"
                        value={term}
                        onChange={(event) => setTerm(event.target.value)}
                        placeholder={t('games.search.placeholder')}
                        autoComplete="off"
                        autoFocus
                        className="w-full rounded-md border border-stone-300 bg-transparent px-3 py-2 outline-none focus:ring-2 focus:ring-stone-400 dark:border-stone-700"
                    />
                    <p
                        className="mt-1 text-sm text-stone-500 dark:text-stone-400"
                        aria-live="polite"
                    >
                        {searching
                            ? t('games.search.searching')
                            : t('games.search.hint', { min: minLength })}
                    </p>
                </form>

                {failed && (
                    <p
                        role="alert"
                        className="mt-6 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200"
                    >
                        {t('games.search.failed')}
                    </p>
                )}

                {showEmpty && (
                    <p className="mt-6 text-stone-600 dark:text-stone-400">
                        {t('games.search.empty', { query })}
                    </p>
                )}

                <ul className="mt-6 divide-y divide-stone-200 dark:divide-stone-800">
                    {results.map((game) => (
                        <li key={game.slug}>
                            <Link
                                href={show(game.slug)}
                                className="flex items-center gap-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900"
                            >
                                <GameCover
                                    imageId={game.cover_image_id}
                                    name={game.name}
                                />
                                <div className="min-w-0">
                                    <p className="font-medium">
                                        {game.name}
                                        {game.year !== null && (
                                            <span className="ml-1 font-normal text-stone-500">
                                                ({game.year})
                                            </span>
                                        )}
                                    </p>
                                    {game.platforms.length > 0 && (
                                        <p className="truncate text-sm text-stone-500 dark:text-stone-400">
                                            {game.platforms.join(' · ')}
                                        </p>
                                    )}
                                </div>
                            </Link>
                        </li>
                    ))}
                </ul>
            </section>
        </AppLayout>
    );
}
