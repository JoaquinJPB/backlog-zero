import { Head, Link, usePage } from '@inertiajs/react';
import { Avatar } from '@/components/app-nav';
import AppLayout from '@/components/app-layout';
import GameTile from '@/components/game-tile';
import { LogOutIcon, SettingsIcon } from '@/components/icons';
import PaginationNav from '@/components/pagination-nav';
import type { Pagination } from '@/components/pagination-nav';
import { formatAverage, formatScore } from '@/components/score';
import { StarRatingDisplay } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import { STATUS_INK } from '@/lib/status';
import { buttonClass } from '@/lib/ui';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { index as journalIndex, show as journalOf } from '@/routes/journal';
import { profile as settingsProfile } from '@/routes/settings';
import type { LibraryEntry } from '@/types/library';
import type { RouteDefinition } from '@/wayfinder';

interface RankedItem {
    id: number;
    name: string;
    count: number;
}

// Mirrors ShowProfileController::statsProps().
interface ProfileStats {
    counts: {
        library: number;
        finished: number;
        finished_this_year: number;
        playing: number;
        backlog: number;
        abandoned: number;
    };
    current_year: number;
    /** 1-10 scale (half stars), one decimal. */
    average_rating: number | null;
    /** Ten counts: index 0 is rating 1 (half a star), index 9 is rating 10. */
    rating_histogram: number[];
    finished_per_year: { year: number; count: number }[];
    top_platforms: RankedItem[];
    top_genres: RankedItem[];
}

interface ProfilePageProps {
    profile: {
        name: string;
        username: string;
        avatar_url: string | null;
    };
    stats: ProfileStats;
    games: LibraryEntry[];
    pagination: Pagination;
}

/** Ruled section: the profile has no boxes, only rules, like the journal. */
const sectionClass = 'border-t border-rule pt-4';
const sectionTitleClass = 'font-label text-sm text-ink-muted';

export default function ProfilePage({
    profile,
    stats,
    games,
    pagination,
}: ProfilePageProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const isOwn = auth.user?.username === profile.username;

    return (
        <AppLayout>
            <Head title={`${profile.name} (@${profile.username})`} />

            <header className="flex flex-wrap items-center gap-x-5 gap-y-4">
                <Avatar
                    url={profile.avatar_url}
                    name={profile.name}
                    className="size-16 shrink-0 text-2xl ring-2 ring-gold/60 sm:size-20"
                />
                <div className="min-w-0 flex-1">
                    <h1 className="line-clamp-2 font-marquee text-3xl break-words sm:text-4xl">
                        {profile.name}
                    </h1>
                    <p className="mt-1 font-score text-base text-gold">
                        @{profile.username}
                    </p>
                </div>
                {isOwn && (
                    <div className="flex w-full gap-2 sm:w-auto">
                        <Link
                            href={settingsProfile()}
                            className={buttonClass(
                                'secondary',
                                'sm',
                                'flex-1 sm:flex-none',
                            )}
                        >
                            <SettingsIcon className="size-4" />
                            {t('profile.edit')}
                        </Link>
                        <Link
                            href={logout()}
                            as="button"
                            className={buttonClass('ghost', 'sm', 'md:hidden')}
                        >
                            <LogOutIcon className="size-4" />
                            {t('auth.logout')}
                        </Link>
                    </div>
                )}
            </header>
            {isOwn && (
                <p className="mt-3 text-sm text-ink-faint">
                    {t('profile.visible_note')}
                </p>
            )}

            <Scoreboard stats={stats} />

            <div className="mt-12 grid gap-x-14 gap-y-10 lg:grid-cols-[1.15fr_1fr]">
                <RatingHistogram
                    histogram={stats.rating_histogram}
                    average={stats.average_rating}
                />
                <div className="grid content-start gap-y-10">
                    <FinishedPerYear
                        years={stats.finished_per_year}
                        journalHref={(year) =>
                            isOwn
                                ? journalIndex({ query: { year } })
                                : journalOf(profile.username, {
                                      query: { year },
                                  })
                        }
                    />
                    <div className="grid gap-x-10 gap-y-10 sm:grid-cols-2 lg:grid-cols-1">
                        <RankedList
                            title={t('profile.top_platforms')}
                            items={stats.top_platforms}
                        />
                        <RankedList
                            title={t('profile.top_genres')}
                            items={stats.top_genres}
                        />
                    </div>
                </div>
            </div>

            <section className={cn(sectionClass, 'mt-14')}>
                <h2 className={sectionTitleClass}>
                    {t('profile.games_title')}
                </h2>
                {games.length === 0 ? (
                    <p className="mt-4 rounded-xl border border-dashed border-rule-strong px-6 py-10 text-center text-ink-muted">
                        {t('profile.games_empty')}
                    </p>
                ) : (
                    <ul className="mt-5 grid grid-cols-3 gap-x-3 gap-y-7 sm:grid-cols-4 sm:gap-x-5 md:grid-cols-5 lg:gap-x-6">
                        {games.map((entry) => (
                            <GameTile key={entry.id} entry={entry} />
                        ))}
                    </ul>
                )}
                <PaginationNav pagination={pagination} />
            </section>
        </AppLayout>
    );
}

/** The counts as ruled figures, each in its status ink. */
function Scoreboard({ stats }: { stats: ProfileStats }) {
    const { t } = useTranslation();
    const { counts } = stats;
    const cells: { label: string; value: number; ink: string }[] = [
        {
            label: t('profile.counts.finished'),
            value: counts.finished,
            ink: STATUS_INK.finished.text,
        },
        {
            label: t('profile.counts.finished_this_year', {
                year: stats.current_year,
            }),
            value: counts.finished_this_year,
            ink: STATUS_INK.finished.text,
        },
        {
            label: t('profile.counts.playing'),
            value: counts.playing,
            ink: STATUS_INK.playing.text,
        },
        {
            label: t('profile.counts.backlog'),
            value: counts.backlog,
            ink: STATUS_INK.backlog.text,
        },
        {
            label: t('profile.counts.abandoned'),
            value: counts.abandoned,
            ink: STATUS_INK.abandoned.text,
        },
        {
            label: t('profile.counts.library'),
            value: counts.library,
            ink: 'text-ink',
        },
    ];

    return (
        <dl className="mt-8 grid grid-cols-2 gap-x-6 sm:grid-cols-3 lg:grid-cols-6">
            {cells.map((cell) => (
                <div
                    key={cell.label}
                    className="flex flex-col border-t border-rule pt-3 pb-5"
                >
                    <dt className="order-2 mt-1.5 text-sm text-ink-muted">
                        {cell.label}
                    </dt>
                    <dd
                        className={cn(
                            'order-1 font-score text-5xl lg:text-6xl',
                            cell.value === 0 ? 'text-ink-faint' : cell.ink,
                        )}
                    >
                        {cell.value}
                    </dd>
                </div>
            ))}
        </dl>
    );
}

function RatingHistogram({
    histogram,
    average,
}: {
    histogram: number[];
    average: number | null;
}) {
    const { t, tc } = useTranslation();
    const rated = histogram.reduce((sum, count) => sum + count, 0);
    const max = Math.max(...histogram, 1);

    return (
        <section className={sectionClass}>
            <h2 className={sectionTitleClass}>{t('profile.ratings_title')}</h2>
            <div className="mt-3 flex flex-wrap items-end gap-x-4 gap-y-2">
                {average === null ? (
                    <p className="text-ink-muted">{t('profile.no_ratings')}</p>
                ) : (
                    <>
                        <span className="font-score text-7xl text-gold sm:text-8xl">
                            {formatAverage(average)}
                        </span>
                        <span className="pb-2">
                            <StarRatingDisplay
                                rating={Math.round(average)}
                                starClassName="size-5"
                            />
                            <span className="mt-1 block text-sm text-ink-muted">
                                {t('profile.average_rating')} ·{' '}
                                {tc('profile.rated_count', rated)}
                            </span>
                        </span>
                    </>
                )}
            </div>

            <ol className="mt-8 flex h-48 items-end gap-1 sm:gap-1.5">
                {histogram.map((count, index) => {
                    const label = t('profile.rating_bar', {
                        score: formatScore(index + 1),
                        count,
                    });

                    return (
                        <li
                            key={index}
                            title={label}
                            className="flex h-full flex-1 flex-col items-center justify-end gap-1.5"
                        >
                            <span className="sr-only">{label}</span>
                            <span
                                aria-hidden="true"
                                className="font-score text-xs text-ink-faint"
                            >
                                {count > 0 ? count : ''}
                            </span>
                            <span
                                aria-hidden="true"
                                className={cn(
                                    'block w-full rounded-t-[3px]',
                                    count > 0 ? 'bg-gold' : 'bg-rule',
                                )}
                                style={{
                                    height: `${(count / max) * 100}%`,
                                    minHeight: 3,
                                }}
                            />
                        </li>
                    );
                })}
            </ol>
            <div
                aria-hidden="true"
                className="mt-2 flex gap-1 border-t border-rule pt-2 sm:gap-1.5"
            >
                {histogram.map((_, index) => (
                    <span
                        key={index}
                        className="flex-1 text-center font-score text-[11px] text-ink-faint"
                    >
                        {formatScore(index + 1)}
                    </span>
                ))}
            </div>
        </section>
    );
}

function FinishedPerYear({
    years,
    journalHref,
}: {
    years: ProfileStats['finished_per_year'];
    journalHref: (year: number) => RouteDefinition<'get'>;
}) {
    const { t, tc } = useTranslation();
    const max = Math.max(...years.map((row) => row.count), 1);

    return (
        <section className={sectionClass}>
            <h2 className={sectionTitleClass}>{t('profile.per_year_title')}</h2>
            {years.length === 0 ? (
                <p className="mt-3 text-sm text-ink-muted">
                    {t('profile.nothing_yet')}
                </p>
            ) : (
                <ul className="mt-3 space-y-1">
                    {years.map((row) => (
                        <li key={row.year}>
                            <Link
                                href={journalHref(row.year)}
                                className="group -mx-2 grid grid-cols-[3.5rem_1fr_auto] items-center gap-3 rounded-md px-2 py-1.5 transition-colors hover:bg-cabinet-raised"
                                aria-label={`${row.year}: ${tc('profile.game_count', row.count)}`}
                            >
                                <span className="font-score text-lg text-ink group-hover:text-gold">
                                    {row.year}
                                </span>
                                <span
                                    aria-hidden="true"
                                    className="h-2 rounded-full bg-gold/80 transition-colors group-hover:bg-gold"
                                    style={{
                                        width: `${(row.count / max) * 100}%`,
                                    }}
                                />
                                <span className="font-score text-lg text-gold">
                                    {row.count}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

function RankedList({ title, items }: { title: string; items: RankedItem[] }) {
    const { t, tc } = useTranslation();
    const max = Math.max(...items.map((item) => item.count), 1);

    return (
        <section className={sectionClass}>
            <h2 className={sectionTitleClass}>{title}</h2>
            {items.length === 0 ? (
                <p className="mt-3 text-sm text-ink-muted">
                    {t('profile.nothing_yet')}
                </p>
            ) : (
                <ol className="mt-3 space-y-3">
                    {items.map((item, index) => (
                        <li
                            key={item.id}
                            className="grid grid-cols-[2rem_1fr] gap-x-2"
                        >
                            <span
                                className={cn(
                                    'row-span-2 font-score text-lg',
                                    index === 0
                                        ? 'text-gold'
                                        : 'text-ink-faint',
                                )}
                            >
                                {index + 1}º
                            </span>
                            <div className="flex justify-between gap-2 text-sm">
                                <span className="truncate font-semibold">
                                    {item.name}
                                </span>
                                <span className="shrink-0 text-ink-muted">
                                    {tc('profile.game_count', item.count)}
                                </span>
                            </div>
                            <div
                                aria-hidden="true"
                                className={cn(
                                    'mt-1.5 h-1.5 rounded-full',
                                    index === 0 ? 'bg-gold' : 'bg-rule-strong',
                                )}
                                style={{
                                    width: `${(item.count / max) * 100}%`,
                                }}
                            />
                        </li>
                    ))}
                </ol>
            )}
        </section>
    );
}
