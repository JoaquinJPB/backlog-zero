import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import PaginationNav from '@/components/pagination-nav';
import type { Pagination } from '@/components/pagination-nav';
import { StarRatingDisplay } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import { show as gameShow } from '@/routes/games';
import { index as journalIndex } from '@/routes/journal';
import { profile as settingsProfile } from '@/routes/settings';
import type { LibraryEntry } from '@/types/library';

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

const cardClass =
    'rounded-lg border border-stone-200 p-4 dark:border-stone-800';

function stars(rating: number): string {
    return (rating / 2).toLocaleString('es-ES', { maximumFractionDigits: 1 });
}

export default function ProfilePage({
    profile,
    stats,
    games,
    pagination,
}: ProfilePageProps) {
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={`${profile.name} (@${profile.username})`} />

            <header className="flex flex-wrap items-center gap-4">
                {profile.avatar_url !== null && (
                    <img
                        src={profile.avatar_url}
                        alt=""
                        referrerPolicy="no-referrer"
                        className="size-16 rounded-full"
                    />
                )}
                <div className="min-w-0 flex-1">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {profile.name}
                    </h1>
                    <p className="text-stone-500">@{profile.username}</p>
                </div>
                <Link
                    href={settingsProfile()}
                    className="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800"
                >
                    {t('profile.edit')}
                </Link>
            </header>
            <p className="mt-2 text-sm text-stone-500">
                {t('profile.private_note')}
            </p>

            <StatTiles stats={stats} />

            <div className="mt-6 grid gap-4 md:grid-cols-2">
                <RatingHistogram histogram={stats.rating_histogram} />
                <FinishedPerYear years={stats.finished_per_year} />
                <RankedList
                    title={t('profile.top_platforms')}
                    items={stats.top_platforms}
                />
                <RankedList
                    title={t('profile.top_genres')}
                    items={stats.top_genres}
                />
            </div>

            <h2 className="mt-10 text-lg font-semibold">
                {t('profile.games_title')}
            </h2>
            {games.length === 0 ? (
                <p className="mt-4 text-stone-600 dark:text-stone-400">
                    {t('profile.games_empty')}
                </p>
            ) : (
                <ul className="mt-4 grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6">
                    {games.map((entry) => (
                        <GameTile key={entry.id} entry={entry} />
                    ))}
                </ul>
            )}
            <PaginationNav pagination={pagination} />
        </AppLayout>
    );
}

function StatTiles({ stats }: { stats: ProfileStats }) {
    const { t } = useTranslation();
    const { counts } = stats;
    const tiles: { label: string; value: number }[] = [
        { label: t('profile.counts.library'), value: counts.library },
        { label: t('profile.counts.finished'), value: counts.finished },
        {
            label: t('profile.counts.finished_this_year', {
                year: stats.current_year,
            }),
            value: counts.finished_this_year,
        },
        { label: t('profile.counts.playing'), value: counts.playing },
        { label: t('profile.counts.backlog'), value: counts.backlog },
        { label: t('profile.counts.abandoned'), value: counts.abandoned },
    ];

    return (
        <dl className="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
            {tiles.map((tile) => (
                <div key={tile.label} className={cardClass}>
                    <dt className="text-sm text-stone-500">{tile.label}</dt>
                    <dd className="mt-1 text-2xl font-semibold tabular-nums">
                        {tile.value}
                    </dd>
                </div>
            ))}
            <div className={`${cardClass} col-span-2`}>
                <dt className="text-sm text-stone-500">
                    {t('profile.average_rating')}
                </dt>
                <dd className="mt-1 flex items-center gap-3">
                    {stats.average_rating === null ? (
                        <span className="text-stone-500">
                            {t('profile.no_ratings')}
                        </span>
                    ) : (
                        <>
                            <span className="text-2xl font-semibold tabular-nums">
                                {stars(stats.average_rating)}
                            </span>
                            <StarRatingDisplay
                                rating={Math.round(stats.average_rating)}
                            />
                        </>
                    )}
                </dd>
            </div>
        </dl>
    );
}

function RatingHistogram({ histogram }: { histogram: number[] }) {
    const { t, tc } = useTranslation();
    const rated = histogram.reduce((sum, count) => sum + count, 0);
    const max = Math.max(...histogram, 1);

    return (
        <section className={cardClass}>
            <h2 className="font-semibold">{t('profile.ratings_title')}</h2>
            <p className="text-sm text-stone-500">
                {tc('profile.rated_count', rated)}
            </p>
            <ol className="mt-4 flex h-32 items-end gap-0.5">
                {histogram.map((count, index) => {
                    const label = t('profile.rating_bar', {
                        stars: stars(index + 1),
                        count,
                    });

                    return (
                        <li
                            key={index}
                            title={label}
                            className="flex h-full flex-1 flex-col justify-end"
                        >
                            <span className="sr-only">{label}</span>
                            <span
                                aria-hidden="true"
                                className="block rounded-t bg-amber-500"
                                style={{
                                    height: `${(count / max) * 100}%`,
                                    minHeight: count > 0 ? 4 : 0,
                                }}
                            />
                        </li>
                    );
                })}
            </ol>
            <div
                aria-hidden="true"
                className="mt-1 flex justify-between border-t border-stone-200 pt-1 text-xs text-stone-500 dark:border-stone-800"
            >
                <span>½★</span>
                <span>5★</span>
            </div>
        </section>
    );
}

function FinishedPerYear({
    years,
}: {
    years: ProfileStats['finished_per_year'];
}) {
    const { t, tc } = useTranslation();

    return (
        <section className={cardClass}>
            <h2 className="font-semibold">{t('profile.per_year_title')}</h2>
            {years.length === 0 ? (
                <p className="mt-2 text-sm text-stone-500">
                    {t('profile.nothing_yet')}
                </p>
            ) : (
                <ul className="mt-2 divide-y divide-stone-200 dark:divide-stone-800">
                    {years.map((row) => (
                        <li key={row.year}>
                            <Link
                                href={journalIndex({
                                    query: { year: row.year },
                                })}
                                className="flex justify-between py-1.5 hover:underline"
                            >
                                <span>{row.year}</span>
                                <span className="text-stone-500 tabular-nums">
                                    {tc('profile.game_count', row.count)}
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
        <section className={cardClass}>
            <h2 className="font-semibold">{title}</h2>
            {items.length === 0 ? (
                <p className="mt-2 text-sm text-stone-500">
                    {t('profile.nothing_yet')}
                </p>
            ) : (
                <ol className="mt-2 space-y-2">
                    {items.map((item) => (
                        <li key={item.id}>
                            <div className="flex justify-between gap-2 text-sm">
                                <span className="truncate">{item.name}</span>
                                <span className="shrink-0 text-stone-500 tabular-nums">
                                    {tc('profile.game_count', item.count)}
                                </span>
                            </div>
                            <div
                                aria-hidden="true"
                                className="mt-1 h-1.5 rounded-full bg-amber-500"
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

function GameTile({ entry }: { entry: LibraryEntry }) {
    const { t } = useTranslation();

    if (entry.game === undefined) {
        return null;
    }

    return (
        <li>
            <Link href={gameShow(entry.game.slug)} className="group block">
                <GameCover
                    imageId={entry.game.cover_image_id}
                    name={entry.game.name}
                    size="big"
                    className="aspect-[264/374] h-auto w-full group-hover:opacity-80"
                />
                <p className="mt-1 truncate text-sm font-medium">
                    {entry.game.name}
                </p>
                <p className="text-xs text-stone-500">
                    {t(`library.statuses.${entry.status}`)}
                    {entry.rating !== null && ` · ${stars(entry.rating)}★`}
                </p>
            </Link>
        </li>
    );
}
