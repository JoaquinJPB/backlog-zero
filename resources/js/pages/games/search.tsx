import { Head, Link, router } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect, useState } from 'react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { AlertIcon, SearchIcon } from '@/components/icons';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { search, show } from '@/routes/games';
import type { GameSummary } from '@/types/games';

interface GameSearchProps {
    query: string;
    results: GameSummary[];
    failed: boolean;
    minLength: number;
}

const DEBOUNCE_MS = 300;
const SKELETON_ROWS = 4;

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
    const showSkeleton = searching && results.length === 0;

    return (
        <AppLayout>
            <Head title={t('games.search.title')} />
            <section className="mx-auto max-w-3xl">
                <h1 className="font-marquee text-[1.9rem] sm:text-5xl">
                    {t('games.search.title')}
                </h1>

                <form onSubmit={submit} role="search" className="mt-6">
                    <label htmlFor="game-search" className="sr-only">
                        {t('games.search.label')}
                    </label>
                    <div className="group relative">
                        <SearchIcon
                            className={cn(
                                'pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-faint transition-colors duration-150 group-focus-within:text-gold',
                                searching && 'animate-pulse text-gold',
                            )}
                        />
                        <input
                            id="game-search"
                            type="search"
                            value={term}
                            onChange={(event) => setTerm(event.target.value)}
                            placeholder={t('games.search.placeholder')}
                            autoComplete="off"
                            enterKeyHint="search"
                            autoFocus
                            className="h-14 w-full rounded-xl border border-rule-strong bg-cabinet-raised pr-4 pl-12 text-lg text-ink shadow-[0_12px_30px_-18px_rgb(0_0_0/0.9)] transition-colors duration-150 outline-none placeholder:text-ink-faint hover:border-ink-faint focus:border-gold focus:ring-4 focus:ring-gold/15"
                        />
                    </div>
                    <p
                        className="mt-2 text-sm text-ink-muted"
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
                        className="mt-6 flex animate-flash-in items-start gap-3 rounded-lg border border-gold/40 bg-gold/10 px-4 py-3 text-sm font-medium text-[#ffe7a0]"
                    >
                        <AlertIcon className="mt-px size-[18px] shrink-0 text-gold" />
                        {t('games.search.failed')}
                    </p>
                )}

                {showEmpty && (
                    <p className="mt-8 rounded-xl border border-dashed border-rule-strong px-6 py-10 text-center text-ink-muted">
                        {t('games.search.empty', { query })}
                    </p>
                )}

                {showSkeleton ? (
                    <SkeletonRows />
                ) : (
                    <ul
                        className={cn(
                            'mt-6 transition-opacity duration-200',
                            searching && 'opacity-50',
                        )}
                    >
                        {results.map((game) => (
                            <ResultRow key={game.slug} game={game} />
                        ))}
                    </ul>
                )}
            </section>
        </AppLayout>
    );
}

function ResultRow({ game }: { game: GameSummary }) {
    return (
        <li className="border-b border-rule">
            <Link
                href={show(game.slug)}
                className="group -mx-3 flex items-center gap-4 rounded-lg px-3 py-3 transition-colors duration-150 hover:bg-cabinet-raised"
            >
                <GameCover
                    imageId={game.cover_image_id}
                    name={game.name}
                    className="transition-transform duration-200 ease-out group-hover:-translate-y-0.5"
                />
                <div className="min-w-0 flex-1">
                    <p className="font-semibold sm:text-[17px]">{game.name}</p>
                    {game.platforms.length > 0 && (
                        <p className="mt-1 line-clamp-1 text-sm text-ink-muted">
                            {game.platforms.join(' · ')}
                        </p>
                    )}
                </div>
                {game.year !== null && (
                    <span className="shrink-0 font-score text-lg text-ink-faint">
                        {game.year}
                    </span>
                )}
            </Link>
        </li>
    );
}

function SkeletonRows() {
    return (
        <ul className="mt-6" aria-hidden="true">
            {Array.from({ length: SKELETON_ROWS }, (_, index) => (
                <li
                    key={index}
                    className="flex animate-pulse items-center gap-4 border-b border-rule py-3"
                >
                    <span className="h-20 w-14 rounded-[5px] bg-cabinet-high" />
                    <span className="flex-1 space-y-2">
                        <span className="block h-4 w-2/3 rounded bg-cabinet-high" />
                        <span className="block h-3 w-1/3 rounded bg-cabinet-high" />
                    </span>
                </li>
            ))}
        </ul>
    );
}
