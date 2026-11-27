import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { StarRatingDisplay } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { show } from '@/routes/games';
import { index } from '@/routes/library';
import type { GameStatus, LibraryEntry } from '@/types/library';
import { GAME_STATUSES } from '@/types/library';

interface Pagination {
    current_page: number;
    last_page: number;
    prev_url: string | null;
    next_url: string | null;
}

interface LibraryPageProps {
    status: GameStatus | null;
    counts: Record<GameStatus | 'all', number>;
    entries: LibraryEntry[];
    pagination: Pagination;
}

function formatDate(date: string): string {
    return new Date(`${date}T00:00:00Z`).toLocaleDateString('es-ES', {
        dateStyle: 'medium',
        timeZone: 'UTC',
    });
}

export default function LibraryPage({
    status,
    counts,
    entries,
    pagination,
}: LibraryPageProps) {
    const { t } = useTranslation();
    const tabs: { key: GameStatus | null; label: string; count: number }[] = [
        { key: null, label: t('library.all'), count: counts.all },
        ...GAME_STATUSES.map((value) => ({
            key: value,
            label: t(`library.statuses.${value}`),
            count: counts[value],
        })),
    ];

    return (
        <AppLayout>
            <Head title={t('library.title')} />
            <h1 className="text-2xl font-semibold tracking-tight">
                {t('library.title')}
            </h1>

            <nav
                className="mt-6 flex flex-wrap gap-2"
                aria-label={t('library.status_label')}
            >
                {tabs.map((tab) => (
                    <Link
                        key={tab.key ?? 'all'}
                        href={index({
                            query: tab.key === null ? {} : { status: tab.key },
                        })}
                        aria-current={tab.key === status ? 'page' : undefined}
                        preserveScroll
                        className={cn(
                            'rounded-full border px-3 py-1 text-sm',
                            tab.key === status
                                ? 'border-stone-900 bg-stone-900 text-white dark:border-stone-100 dark:bg-stone-100 dark:text-stone-900'
                                : 'border-stone-300 hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800',
                        )}
                    >
                        {tab.label}{' '}
                        <span className="opacity-60">{tab.count}</span>
                    </Link>
                ))}
            </nav>

            {entries.length === 0 ? (
                <p className="mt-10 text-stone-600 dark:text-stone-400">
                    {t('library.empty')}
                </p>
            ) : (
                <ul className="mt-6 divide-y divide-stone-200 dark:divide-stone-800">
                    {entries.map((entry) => (
                        <LibraryRow key={entry.id} entry={entry} />
                    ))}
                </ul>
            )}

            {pagination.last_page > 1 && (
                <nav className="mt-8 flex items-center justify-between text-sm">
                    {pagination.prev_url !== null ? (
                        <Link
                            href={pagination.prev_url}
                            className="underline underline-offset-4"
                        >
                            {t('library.previous_page')}
                        </Link>
                    ) : (
                        <span />
                    )}
                    <span className="text-stone-500">
                        {t('library.page_of', {
                            current: pagination.current_page,
                            last: pagination.last_page,
                        })}
                    </span>
                    {pagination.next_url !== null ? (
                        <Link
                            href={pagination.next_url}
                            className="underline underline-offset-4"
                        >
                            {t('library.next_page')}
                        </Link>
                    ) : (
                        <span />
                    )}
                </nav>
            )}
        </AppLayout>
    );
}

function LibraryRow({ entry }: { entry: LibraryEntry }) {
    const { t } = useTranslation();

    if (entry.game === undefined) {
        return null;
    }

    const endLabel =
        entry.status === 'abandoned' ? 'library.abandoned' : 'library.finished';

    return (
        <li>
            <Link
                href={show(entry.game.slug)}
                className="flex items-center gap-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900"
            >
                <GameCover
                    imageId={entry.game.cover_image_id}
                    name={entry.game.name}
                />
                <div className="min-w-0 flex-1">
                    <p className="font-medium">
                        {entry.game.name}
                        {entry.game.year !== null && (
                            <span className="ml-1 font-normal text-stone-500">
                                ({entry.game.year})
                            </span>
                        )}
                    </p>
                    <p className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500 dark:text-stone-400">
                        <span className="rounded bg-stone-200 px-1.5 py-0.5 text-xs font-medium text-stone-700 dark:bg-stone-800 dark:text-stone-300">
                            {t(`library.statuses.${entry.status}`)}
                        </span>
                        {entry.platform && <span>{entry.platform}</span>}
                        {entry.started_on !== null && (
                            <span>
                                {t('library.started', {
                                    date: formatDate(entry.started_on),
                                })}
                            </span>
                        )}
                        {entry.finished_on !== null && (
                            <span>
                                {t(endLabel, {
                                    date: formatDate(entry.finished_on),
                                })}
                            </span>
                        )}
                    </p>
                </div>
                {entry.rating !== null && (
                    <StarRatingDisplay
                        rating={entry.rating}
                        className="shrink-0"
                    />
                )}
            </Link>
        </li>
    );
}
