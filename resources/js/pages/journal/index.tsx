import { Head, Link, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AbandonedStamp, {
    abandonedTitleClass,
} from '@/components/abandoned-stamp';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { JournalIcon, SearchIcon } from '@/components/icons';
import Score, { starsDecimal } from '@/components/score';
import Select from '@/components/select';
import CompletedBadge from '@/components/completed-badge';
import { StarRatingDisplay } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import { buttonClass, formatDate, pillClass } from '@/lib/ui';
import { cn } from '@/lib/utils';
import { search, show } from '@/routes/games';
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
    playing: LibraryEntry[];
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
    return new Date(Date.UTC(2000, month - 1, 1)).toLocaleDateString('es-ES', {
        month: 'long',
        timeZone: 'UTC',
    });
}

function daysBetween(start: string, end: string): number {
    return Math.round((Date.parse(end) - Date.parse(start)) / DAY_MS);
}

/**
 * Finished games get their place in the year: months arrive newest first,
 * so the newest finished game carries the highest ordinal.
 */
function ordinals(
    months: JournalMonth[],
    finished: number,
): Map<number, number> {
    const ids = months
        .flatMap((month) => month.entries)
        .filter((entry) => entry.status === 'finished')
        .map((entry) => entry.id);

    return new Map(ids.map((id, position) => [id, finished - position]));
}

export default function Journal({
    year,
    years,
    filters,
    platforms,
    summary,
    months,
    playing,
}: JournalProps) {
    const { t } = useTranslation();
    const ranks = ordinals(months, summary.finished);

    // Filters apply within the selected year; switching year (the pills
    // below) drops the platform, which may not exist in the other year.
    function applyFilters(next: Partial<JournalFilters>): void {
        router.get(index.url(), query(year, { ...filters, ...next }), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    if (years.length === 0) {
        return (
            <AppLayout>
                <Head title={t('journal.title', { year })} />
                <div className="mx-auto max-w-md">
                    <NowPlaying entries={playing} />
                </div>
                <JournalEmpty />
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <Head title={t('journal.title', { year })} />
            <div className="grid gap-10 lg:grid-cols-[300px_1fr] lg:gap-14">
                <aside className="lg:sticky lg:top-28 lg:self-start">
                    <h1 className="font-marquee text-[2.05rem] leading-none sm:text-6xl sm:leading-[0.9] lg:text-[3.4rem]">
                        {t('journal.nav')}{' '}
                        <span className="text-gold lg:block">{year}</span>
                    </h1>

                    <nav
                        aria-label={t('journal.year_label')}
                        className="-mx-4 mt-4 flex [scrollbar-width:none] gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0"
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
                                className={pillClass(option.year === year)}
                            >
                                <span className="font-score">
                                    {option.year}
                                </span>
                                <span className="font-score text-xs opacity-60">
                                    {option.count}
                                </span>
                            </Link>
                        ))}
                    </nav>

                    <NowPlaying entries={playing} />
                    <Figures summary={summary} />
                </aside>

                <section aria-label={t('journal.title', { year })}>
                    <Filters
                        platforms={platforms}
                        filters={filters}
                        onChange={applyFilters}
                    />

                    {months.length === 0 ? (
                        <p className="mt-10 rounded-xl border border-dashed border-rule-strong px-6 py-10 text-center text-ink-muted">
                            {t('journal.empty', { year })}
                        </p>
                    ) : (
                        months.map((month) => (
                            <MonthTable
                                key={month.month}
                                month={month}
                                ranks={ranks}
                            />
                        ))
                    )}
                </section>
            </div>
        </AppLayout>
    );
}

/** One figure owns each slot: the year's headline numbers. */
function Figures({ summary }: { summary: JournalProps['summary'] }) {
    const { t } = useTranslation();

    return (
        <dl className="mt-5 grid grid-cols-3 gap-4 lg:mt-8 lg:grid-cols-1 lg:gap-0">
            <Figure
                label={t('journal.figures.finished')}
                value={String(summary.finished)}
                className="text-gold"
            />
            <Figure
                label={t('journal.figures.abandoned')}
                value={String(summary.abandoned)}
                className={
                    summary.abandoned > 0 ? 'text-red' : 'text-ink-faint'
                }
            />
            <Figure
                label={t('journal.figures.average')}
                value={
                    summary.average_rating === null
                        ? '–'
                        : starsDecimal(summary.average_rating)
                }
                className="text-gold"
                extra={
                    summary.average_rating !== null && (
                        <StarRatingDisplay
                            rating={Math.round(summary.average_rating)}
                            className="mt-2 hidden lg:inline-flex"
                        />
                    )
                }
            />
        </dl>
    );
}

interface FigureProps {
    label: string;
    value: string;
    className: string;
    extra?: ReactNode;
}

function Figure({ label, value, className, extra }: FigureProps) {
    return (
        <div className="flex flex-col border-t border-rule pt-3 lg:py-5">
            <dt className="order-2 mt-1.5 text-sm text-ink-muted">{label}</dt>
            <dd
                className={cn(
                    'order-1 font-score text-4xl sm:text-5xl lg:text-6xl',
                    className,
                )}
            >
                {value}
            </dd>
            {extra !== undefined && <dd className="order-3">{extra}</dd>}
        </div>
    );
}

interface FiltersProps {
    platforms: PlatformOption[];
    filters: JournalFilters;
    onChange: (next: Partial<JournalFilters>) => void;
}

function Filters({ platforms, filters, onChange }: FiltersProps) {
    const { t } = useTranslation();

    return (
        <div className="flex items-center gap-x-4 gap-y-3 border-b border-rule pb-5 sm:flex-wrap sm:gap-x-6">
            {platforms.length > 0 && (
                <label className="flex min-w-0 flex-1 items-center gap-3 text-sm font-semibold text-ink-muted sm:flex-none">
                    <span className="max-sm:sr-only">
                        {t('journal.platform_label')}
                    </span>
                    <Select
                        value={filters.platform ?? ''}
                        onChange={(event) =>
                            onChange({
                                platform:
                                    event.target.value === ''
                                        ? null
                                        : Number(event.target.value),
                            })
                        }
                        wrapperClassName="min-w-0 flex-1 sm:w-auto sm:flex-none"
                        className="mt-0 h-10 sm:min-w-44"
                    >
                        <option value="">{t('journal.any_platform')}</option>
                        {platforms.map((platform) => (
                            <option key={platform.id} value={platform.id}>
                                {platform.name}
                            </option>
                        ))}
                    </Select>
                </label>
            )}
            <label className="flex shrink-0 cursor-pointer items-center gap-2.5 text-sm font-semibold text-ink-muted select-none hover:text-ink">
                <input
                    type="checkbox"
                    checked={filters.abandoned}
                    onChange={(event) =>
                        onChange({ abandoned: event.target.checked })
                    }
                    className="size-4.5 cursor-pointer rounded accent-red"
                />
                <span className="sm:hidden">
                    {t('journal.include_abandoned_short')}
                </span>
                <span className="max-sm:hidden">
                    {t('journal.include_abandoned')}
                </span>
            </label>
        </div>
    );
}

function MonthTable({
    month,
    ranks,
}: {
    month: JournalMonth;
    ranks: Map<number, number>;
}) {
    const { tc } = useTranslation();

    return (
        <section className="mt-9 first-of-type:mt-7">
            <h2 className="flex items-baseline justify-between gap-3 pb-2">
                <span className="font-label text-lg text-ink">
                    {monthName(month.month)}
                </span>
                <span className="text-sm text-ink-faint">
                    {tc('journal.month_count', month.entries.length)}
                </span>
            </h2>
            <ol className="border-t border-rule">
                {month.entries.map((entry) => (
                    <JournalRow
                        key={entry.id}
                        entry={entry}
                        rank={ranks.get(entry.id) ?? null}
                    />
                ))}
            </ol>
        </section>
    );
}

function JournalRow({
    entry,
    rank,
}: {
    entry: LibraryEntry;
    rank: number | null;
}) {
    const { t, tc } = useTranslation();

    if (entry.game === undefined || entry.finished_on === null) {
        return null;
    }

    const abandoned = entry.status === 'abandoned';
    const date = formatDate(entry.finished_on, {
        day: 'numeric',
        month: 'short',
    });
    const took =
        entry.started_on === null
            ? null
            : tc(
                  'journal.took_days',
                  daysBetween(entry.started_on, entry.finished_on),
              );

    return (
        <li className="border-b border-rule">
            <Link
                href={show(entry.game.slug)}
                className="group -mx-3 grid grid-cols-[2.5rem_auto_1fr_auto] items-center gap-x-3 rounded-lg px-3 py-3 transition-colors duration-150 hover:bg-cabinet-raised sm:grid-cols-[3.5rem_auto_1fr_10rem_4.5rem] sm:gap-x-4"
            >
                <span
                    className={cn(
                        'font-score text-lg sm:text-2xl',
                        abandoned ? 'text-ink-faint' : 'text-ink-muted',
                    )}
                >
                    {rank === null ? '—' : `${rank}º`}
                </span>
                <GameCover
                    imageId={entry.game.cover_image_id}
                    name={entry.game.name}
                    size="xs"
                    className={cn(
                        'transition-transform duration-200 ease-out group-hover:-translate-y-0.5 sm:h-16 sm:w-[2.85rem]',
                        abandoned && 'opacity-55 grayscale',
                    )}
                />
                <div className="min-w-0">
                    <div className="flex items-start gap-2">
                        <p
                            className={cn(
                                'line-clamp-2 min-w-0 leading-snug font-semibold sm:line-clamp-1',
                                abandoned && abandonedTitleClass,
                            )}
                        >
                            {entry.game.name}
                        </p>
                        {entry.completed && (
                            <CompletedBadge className="mt-px shrink-0" />
                        )}
                    </div>
                    <p className="mt-0.5 truncate text-sm text-ink-muted">
                        {/* Phones keep one short line: date · days. */}
                        <span className="sm:hidden">{date}</span>
                        <span className="hidden sm:inline">
                            {t(
                                abandoned
                                    ? 'journal.abandoned_on'
                                    : 'journal.finished_on',
                                { date },
                            )}
                        </span>
                        {took !== null && ` · ${took}`}
                    </p>
                </div>
                <span className="hidden truncate text-sm text-ink-muted sm:block">
                    {entry.platform}
                </span>
                {abandoned ? (
                    <span className="justify-self-end">
                        <AbandonedStamp />
                    </span>
                ) : (
                    <Score
                        rating={entry.rating}
                        status={entry.status}
                        className="text-right text-2xl sm:text-3xl"
                    />
                )}
            </Link>
        </li>
    );
}

/** What is on the cabinet right now: the only place cyan lives. */
function NowPlaying({ entries }: { entries: LibraryEntry[] }) {
    const { t } = useTranslation();

    if (entries.length === 0) {
        return null;
    }

    return (
        <section
            aria-labelledby="now-playing"
            className="mt-4 flex items-start gap-3 border-y border-cyan/30 py-2 lg:mt-6 lg:block lg:rounded-xl lg:border lg:border-cyan/35 lg:bg-cyan/[0.06] lg:p-3"
        >
            <h2
                id="now-playing"
                className="flex shrink-0 items-center gap-2 font-label text-xs text-cyan lg:px-1"
            >
                <span
                    aria-hidden="true"
                    className="size-2 rounded-full bg-cyan"
                />
                {t('journal.now_playing')}
            </h2>
            <ul className="min-w-0 flex-1 lg:mt-2 lg:space-y-1">
                {entries.map((entry) =>
                    entry.game === undefined ? null : (
                        <li
                            key={entry.id}
                            // Phones show only the latest, on one line.
                            className="max-lg:not-first:hidden"
                        >
                            <Link
                                href={show(entry.game.slug)}
                                className="flex items-center gap-3 rounded-lg transition-colors duration-150 hover:bg-cyan/10 lg:p-1"
                            >
                                <GameCover
                                    imageId={entry.game.cover_image_id}
                                    name={entry.game.name}
                                    size="xs"
                                    className="max-lg:hidden"
                                />
                                <span className="min-w-0">
                                    <span className="line-clamp-2 text-sm leading-snug font-semibold">
                                        {entry.game.name}
                                    </span>
                                    {entry.platform && (
                                        <span className="block truncate text-xs text-ink-muted max-lg:hidden">
                                            {entry.platform}
                                        </span>
                                    )}
                                </span>
                            </Link>
                        </li>
                    ),
                )}
            </ul>
        </section>
    );
}

function JournalEmpty() {
    const { t } = useTranslation();

    return (
        <section className="mx-auto max-w-lg py-10 text-center sm:py-16">
            <span className="mx-auto grid size-16 place-items-center rounded-2xl border border-rule-strong bg-cabinet-raised text-gold">
                <JournalIcon className="size-8" />
            </span>
            <h1 className="mt-6 font-marquee text-4xl">{t('journal.nav')}</h1>
            <p className="mt-3 text-ink-muted">{t('journal.empty_all')}</p>
            <Link
                href={search()}
                className={buttonClass('primary', 'md', 'mt-8')}
            >
                <SearchIcon className="size-[18px]" />
                {t('games.search.nav')}
            </Link>
        </section>
    );
}
