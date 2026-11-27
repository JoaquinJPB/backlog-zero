import { Link } from '@inertiajs/react';
import AbandonedStamp, {
    abandonedTitleClass,
} from '@/components/abandoned-stamp';
import GameCover from '@/components/game-cover';
import Score from '@/components/score';
import StatusBadge from '@/components/status-badge';
import { useTranslation } from '@/lib/i18n';
import { formatDate } from '@/lib/ui';
import { cn } from '@/lib/utils';
import { show } from '@/routes/games';
import type { LibraryEntry } from '@/types/library';

/**
 * One library entry as a high-score row: cover, title, status, and the
 * rating as a score in the status ink. Abandoned rows are struck and stamped.
 */
export default function GameRow({ entry }: { entry: LibraryEntry }) {
    const { t } = useTranslation();

    if (entry.game === undefined) {
        return null;
    }

    const abandoned = entry.status === 'abandoned';
    const endLabel = abandoned ? 'library.abandoned' : 'library.finished';

    return (
        <li className="border-b border-rule">
            <Link
                href={show(entry.game.slug)}
                className="group -mx-3 flex items-center gap-4 rounded-lg px-3 py-3.5 transition-colors duration-150 hover:bg-cabinet-raised"
            >
                <GameCover
                    imageId={entry.game.cover_image_id}
                    name={entry.game.name}
                    className={cn(
                        'transition-transform duration-200 ease-out group-hover:-translate-y-0.5',
                        abandoned && 'opacity-55 grayscale',
                    )}
                />
                <div className="min-w-0 flex-1">
                    <p
                        className={cn(
                            'line-clamp-2 leading-snug font-semibold sm:line-clamp-1 sm:text-[17px]',
                            abandoned && abandonedTitleClass,
                        )}
                    >
                        {entry.game.name}
                        {entry.game.year !== null && (
                            <span className="ml-2 font-score text-sm font-normal text-ink-faint no-underline">
                                {entry.game.year}
                            </span>
                        )}
                    </p>
                    <div className="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm text-ink-muted">
                        {!abandoned && <StatusBadge status={entry.status} />}
                        {entry.platform && (
                            <span className="truncate">{entry.platform}</span>
                        )}
                        {entry.started_on !== null && (
                            <span className="hidden sm:inline">
                                {t('library.started', {
                                    date: formatDate(entry.started_on),
                                })}
                            </span>
                        )}
                        {entry.finished_on !== null && (
                            <span className="hidden sm:inline">
                                {t(endLabel, {
                                    date: formatDate(entry.finished_on),
                                })}
                            </span>
                        )}
                    </div>
                </div>
                {abandoned ? (
                    <AbandonedStamp />
                ) : (
                    entry.rating !== null && (
                        <Score
                            rating={entry.rating}
                            status={entry.status}
                            className="shrink-0 text-2xl sm:text-3xl"
                        />
                    )
                )}
            </Link>
        </li>
    );
}
