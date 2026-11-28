import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import GameRow from '@/components/game-row';
import { LibraryIcon, SearchIcon } from '@/components/icons';
import PaginationNav from '@/components/pagination-nav';
import type { Pagination } from '@/components/pagination-nav';
import { useTranslation } from '@/lib/i18n';
import { STATUS_INK } from '@/lib/status';
import { buttonClass, pillClass } from '@/lib/ui';
import { cn } from '@/lib/utils';
import { search } from '@/routes/games';
import { index, show } from '@/routes/library';
import type { GameStatus, LibraryEntry } from '@/types/library';
import type { RouteQueryOptions } from '@/wayfinder';
import { GAME_STATUSES } from '@/types/library';

interface LibraryPageProps {
    owner: {
        name: string;
        username: string;
        avatar_url: string | null;
    };
    status: GameStatus | null;
    counts: Record<GameStatus | 'all', number>;
    entries: LibraryEntry[];
    pagination: Pagination;
}

export default function LibraryPage({
    owner,
    status,
    counts,
    entries,
    pagination,
}: LibraryPageProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const isOwn = auth.user?.username === owner.username;
    const title = isOwn
        ? t('library.title')
        : t('library.title_of', { name: `@${owner.username}` });
    // Keep the status tabs on the library being looked at.
    const tabsRoute = (options: RouteQueryOptions) =>
        isOwn ? index(options) : show(owner.username, options);
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
            <Head title={title} />
            <header className="flex items-baseline justify-between gap-4">
                <h1 className="font-marquee text-[1.7rem] leading-tight sm:text-5xl">
                    {title}
                </h1>
                <span className="font-score text-3xl text-ink-faint sm:text-5xl">
                    {counts.all}
                </span>
            </header>

            <nav
                className="-mx-4 mt-6 flex [scrollbar-width:none] gap-2 overflow-x-auto border-b border-rule px-4 pb-5 sm:mx-0 sm:flex-wrap sm:px-0"
                aria-label={t('library.status_label')}
            >
                {tabs.map((tab) => {
                    const current = tab.key === status;

                    return (
                        <Link
                            key={tab.key ?? 'all'}
                            href={tabsRoute({
                                query:
                                    tab.key === null ? {} : { status: tab.key },
                            })}
                            aria-current={current ? 'page' : undefined}
                            preserveScroll
                            className={pillClass(current)}
                        >
                            {tab.key !== null && (
                                <span
                                    aria-hidden="true"
                                    className={cn(
                                        'size-2 rounded-full',
                                        STATUS_INK[tab.key].dot,
                                    )}
                                />
                            )}
                            {tab.label}
                            <span className="font-score text-xs opacity-60">
                                {tab.count}
                            </span>
                        </Link>
                    );
                })}
            </nav>

            {entries.length === 0 ? (
                <LibraryEmpty owner={isOwn ? null : `@${owner.username}`} />
            ) : (
                <ul>
                    {entries.map((entry) => (
                        <GameRow key={entry.id} entry={entry} />
                    ))}
                </ul>
            )}

            <PaginationNav pagination={pagination} />
        </AppLayout>
    );
}

/** `owner` is the other user's @username, or null when it's your own. */
function LibraryEmpty({ owner }: { owner: string | null }) {
    const { t } = useTranslation();

    return (
        <div className="mt-8 flex flex-col items-center rounded-xl border border-dashed border-rule-strong px-6 py-12 text-center">
            <LibraryIcon className="size-9 text-ink-faint" />
            <p className="mt-4 max-w-sm text-ink-muted">
                {owner === null
                    ? t('library.empty')
                    : t('library.empty_of', { name: owner })}
            </p>
            {owner === null && (
                <Link
                    href={search()}
                    className={buttonClass('primary', 'md', 'mt-6')}
                >
                    <SearchIcon className="size-[18px]" />
                    {t('games.search.nav')}
                </Link>
            )}
        </div>
    );
}
