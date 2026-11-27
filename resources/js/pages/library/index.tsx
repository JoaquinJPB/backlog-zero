import { Head, Link } from '@inertiajs/react';
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
import { index } from '@/routes/library';
import type { GameStatus, LibraryEntry } from '@/types/library';
import { GAME_STATUSES } from '@/types/library';

interface LibraryPageProps {
    status: GameStatus | null;
    counts: Record<GameStatus | 'all', number>;
    entries: LibraryEntry[];
    pagination: Pagination;
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
            <header className="flex items-baseline justify-between gap-4">
                <h1 className="font-marquee text-[1.7rem] leading-tight sm:text-5xl">
                    {t('library.title')}
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
                            href={index({
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
                <LibraryEmpty />
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

function LibraryEmpty() {
    const { t } = useTranslation();

    return (
        <div className="mt-8 flex flex-col items-center rounded-xl border border-dashed border-rule-strong px-6 py-12 text-center">
            <LibraryIcon className="size-9 text-ink-faint" />
            <p className="mt-4 max-w-sm text-ink-muted">{t('library.empty')}</p>
            <Link
                href={search()}
                className={buttonClass('primary', 'md', 'mt-6')}
            >
                <SearchIcon className="size-[18px]" />
                {t('games.search.nav')}
            </Link>
        </div>
    );
}
