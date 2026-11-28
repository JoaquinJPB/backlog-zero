import { Link } from '@inertiajs/react';
import AbandonedStamp, {
    abandonedTitleClass,
} from '@/components/abandoned-stamp';
import CompletedBadge from '@/components/completed-badge';
import GameCover from '@/components/game-cover';
import Score from '@/components/score';
import StatusBadge from '@/components/status-badge';
import { cn } from '@/lib/utils';
import { show } from '@/routes/games';
import type { LibraryEntry } from '@/types/library';

/**
 * One library entry as a shelf tile: the cover leads, title and status
 * below, the score in the status ink. Abandoned tiles are struck and
 * stamped; a 100% game wears its trophy on the cover's corner.
 */
export default function GameTile({ entry }: { entry: LibraryEntry }) {
    if (entry.game === undefined) {
        return null;
    }

    const abandoned = entry.status === 'abandoned';

    return (
        <li>
            <Link
                href={show(entry.game.slug)}
                className="group block rounded-lg focus-visible:outline-offset-4"
            >
                <div className="relative">
                    <GameCover
                        imageId={entry.game.cover_image_id}
                        name={entry.game.name}
                        size="big"
                        className={cn(
                            'transition-transform duration-200 ease-out group-hover:-translate-y-1',
                            abandoned && 'opacity-55 grayscale',
                        )}
                    />
                    {entry.completed && (
                        <span className="absolute top-1.5 right-1.5 grid size-8 place-items-center rounded-full bg-cabinet/85 ring-1 ring-gold/45 backdrop-blur-sm transition-transform duration-200 ease-out group-hover:-translate-y-1">
                            <CompletedBadge />
                        </span>
                    )}
                </div>
                <p
                    className={cn(
                        'mt-2.5 line-clamp-2 text-sm leading-snug font-semibold transition-colors duration-150 group-hover:text-gold',
                        abandoned && abandonedTitleClass,
                    )}
                >
                    {entry.game.name}
                </p>
                <div className="mt-1.5 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
                    {abandoned ? (
                        <AbandonedStamp />
                    ) : (
                        <StatusBadge status={entry.status} />
                    )}
                    {!abandoned && entry.rating !== null && (
                        <Score
                            rating={entry.rating}
                            status={entry.status}
                            className="text-xl"
                        />
                    )}
                </div>
            </Link>
        </li>
    );
}
