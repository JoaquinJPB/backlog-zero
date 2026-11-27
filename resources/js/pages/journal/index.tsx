import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { StarRatingDisplay } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { show } from '@/routes/games';
import { index } from '@/routes/journal';
import type { LibraryEntry, PlatformOption } from '@/types/library';

interface JournalFilters {
    platform: number | null;
    abandoned: boolean;
}

interface JournalMonth {
    month: number;
    entries: LibraryEntry[];
}

interface JournalProps {
    year: number;
    years: { year: number; count: number }[];
    filters: JournalFilters;
    platforms: PlatformOption[];
    summary: {
        finished: number;
        abandoned: number;
        average_rating: number | null;
    };
    months: JournalMonth[];
}

type JournalQuery = Record<string, string | number>;

const DAY_MS = 86_400_000;

function query(year: number, filters: JournalFilters): JournalQuery {
    const params: JournalQuery = { year };

    if (filters.platform !== null) {
        params.platform = filters.platform;
    }

    if (filters.abandoned) {
        params.abandoned = 1;
    }

    return params;
}

function monthName(month: number): string {
    const name = new Date(Date.UTC(2000, month - 1, 1)).toLocaleDateString(
        'es-ES',
        {
            month: 'long',
            timeZone: 'UTC',
        },
    );

    return name.charAt(0).toUpperCase() + name.slice(1);
}

function formatDay(date: string): string {
    return new Date(`${date}T00:00:00Z`).toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short',
        timeZone: 'UTC',
    });
}

function daysBetween(start: string, end: string): number {
    return Math.round((Date.parse(end) - Date.parse(start)) / DAY_MS);
}

export default function Journal({
    year,
    years,
    filters,
    platforms,
    summary,
    months,
}: JournalProps) {
    const { t, tc } = useTranslation();

    // Filters apply within the selected year; switching year (the pills
    // below) drops the platform, which may not exist in the other year.
    function applyFilters(next: Partial<JournalFilters>): void {
        router.get(index.url(), query(year, { ...filters, ...next }), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    return (
        <AppLayout>
            <Head title={t('journal.title', { year })} />
            <h1 className="text-2xl font-semibold tracking-tight">
                {t('journal.title', { year })}
            </h1>

            {years.length === 0 ? (
                <p className="mt-6 text-stone-600 dark:text-stone-400">
                    {t('journal.empty_all')}
                </p>
            ) : (
                <>
                    <nav
                        aria-label={t('journal.year_label')}
                        className="mt-6 flex flex-wrap gap-2"
                    >
                        {years.map((option) => (
                            <Link
                                key={option.year}
                                href={index({
                                    query: query(option.year, {
                                        platform: null,
                                        abandoned: filters.abandoned,
                                    }),
                                })}
                                aria-current={
                                    option.year === year ? 'page' : undefined
                                }
                                preserveScroll
                                className={cn(
                                    'rounded-full border px-3 py-1 text-sm',
                                    option.year === year
                                        ? 'border-stone-900 bg-stone-900 text-white dark:border-stone-100 dark:bg-stone-100 dark:text-stone-900'
                                        : 'border-stone-300 hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800',
                                )}
                            >
                                {option.year}{' '}
                                <span className="opacity-60">
                                    {option.count}
                                </span>
                            </Link>
                        ))}
                    </nav>

                    <div className="mt-4 flex flex-wrap items-center gap-4 text-sm">
                        {platforms.length > 0 && (
                            <label className="flex items-center gap-2">
                                {t('journal.platform_label')}
                                <select
                                    value={filters.platform ?? ''}
                                    onChange={(event) =>
                                        applyFilters({
                                            platform:
                                                event.target.value === ''
                                                    ? null
                                                    : Number(
                                                          event.target.value,
                                                      ),
                                        })
                                    }
                                    className="rounded-md border border-stone-300 bg-transparent px-2 py-1 dark:border-stone-700"
                                >
                                    <option value="">
                                        {t('journal.all_platforms')}
                                    </option>
                                    {platforms.map((platform) => (
                                        <option
                                            key={platform.id}
                                            value={platform.id}
                                        >
                                            {platform.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        )}
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={filters.abandoned}
                                onChange={(event) =>
                                    applyFilters({
                                        abandoned: event.target.checked,
                                    })
                                }
                            />
                            {t('journal.include_abandoned')}
                        </label>
                    </div>

                    <p className="mt-6 flex flex-wrap items-center gap-x-4 gap-y-1 text-stone-600 dark:text-stone-400">
                        <span className="font-medium text-stone-900 dark:text-stone-100">
                            {tc('journal.finished_count', summary.finished)}
                        </span>
                        {summary.abandoned > 0 && (
                            <span>
                                {tc(
                                    'journal.abandoned_count',
                                    summary.abandoned,
                                )}
                            </span>
                        )}
                        {summary.average_rating !== null && (
                            <span>
                                {t('journal.average_rating', {
                                    stars: (
                                        summary.average_rating / 2
                                    ).toLocaleString('es-ES', {
                                        maximumFractionDigits: 1,
                                    }),
                                })}
                            </span>
                        )}
                    </p>

                    {months.length === 0 ? (
                        <p className="mt-8 text-stone-600 dark:text-stone-400">
                            {t('journal.empty', { year })}
                        </p>
                    ) : (
                        months.map((month) => (
                            <section key={month.month} className="mt-8">
                                <h2 className="flex items-baseline gap-2 border-b border-stone-200 pb-2 text-lg font-semibold dark:border-stone-800">
                                    {monthName(month.month)}
                                    <span className="text-sm font-normal text-stone-500">
                                        {tc(
                                            'journal.month_count',
                                            month.entries.length,
                                        )}
                                    </span>
                                </h2>
                                <ul className="divide-y divide-stone-200 dark:divide-stone-800">
                                    {month.entries.map((entry) => (
                                        <JournalRow
                                            key={entry.id}
                                            entry={entry}
                                        />
                                    ))}
                                </ul>
                            </section>
                        ))
                    )}
                </>
            )}
        </AppLayout>
    );
}

function JournalRow({ entry }: { entry: LibraryEntry }) {
    const { t, tc } = useTranslation();

    if (entry.game === undefined || entry.finished_on === null) {
        return null;
    }

    const abandoned = entry.status === 'abandoned';

    return (
        <li>
            <Link
                href={show(entry.game.slug)}
                className="flex items-center gap-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900"
            >
                <GameCover
                    imageId={entry.game.cover_image_id}
                    name={entry.game.name}
                    className={abandoned ? 'opacity-60 grayscale' : undefined}
                />
                <div className="min-w-0 flex-1">
                    <p className="font-medium">{entry.game.name}</p>
                    <p className="mt-0.5 flex flex-wrap gap-x-3 text-sm text-stone-500 dark:text-stone-400">
                        <span>
                            {t(
                                abandoned
                                    ? 'journal.abandoned_on'
                                    : 'journal.finished_on',
                                {
                                    date: formatDay(entry.finished_on),
                                },
                            )}
                            {entry.started_on !== null &&
                                ` · ${tc('journal.took_days', daysBetween(entry.started_on, entry.finished_on))}`}
                        </span>
                        {entry.platform && <span>{entry.platform}</span>}
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
