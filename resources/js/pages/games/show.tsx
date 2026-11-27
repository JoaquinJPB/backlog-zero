import { Head } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { ExternalIcon } from '@/components/icons';
import LibraryEntryForm from '@/components/library-entry-form';
import Score from '@/components/score';
import StatusBadge from '@/components/status-badge';
import { igdbImageUrl } from '@/lib/igdb';
import { useTranslation } from '@/lib/i18n';
import { formatDate } from '@/lib/ui';
import type { GameDetail } from '@/types/games';
import type { LibraryEntry, PlatformOption } from '@/types/library';

interface GameLibraryProps {
    entry: LibraryEntry | null;
    platforms: PlatformOption[];
}

interface ShowGameProps {
    game: GameDetail;
    /** Null for guests. */
    library: GameLibraryProps | null;
}

export default function ShowGame({ game, library }: ShowGameProps) {
    const { t } = useTranslation();
    const entry = library?.entry ?? null;

    return (
        <AppLayout>
            <Head title={game.name} />
            <article className="relative">
                {game.cover_image_id !== null && (
                    <Backlight imageId={game.cover_image_id} />
                )}

                <div className="relative grid gap-x-12 gap-y-8 md:grid-cols-[240px_1fr] lg:grid-cols-[264px_1fr]">
                    <div className="flex gap-5 md:block">
                        <GameCover
                            imageId={game.cover_image_id}
                            name={game.name}
                            size="big"
                            priority
                            className="w-32 shrink-0 shadow-[0_30px_60px_-24px_rgb(0_0_0/0.9)] sm:w-40 md:w-full"
                        />
                        <div className="min-w-0 md:hidden">
                            <Title game={game} />
                            {entry !== null && <EntrySummary entry={entry} />}
                        </div>
                    </div>

                    <div className="min-w-0">
                        <div className="hidden md:block">
                            <Title game={game} />
                            {entry !== null && <EntrySummary entry={entry} />}
                        </div>

                        <Facts game={game} />

                        <p className="mt-6 max-w-[68ch] leading-relaxed whitespace-pre-line text-ink-muted">
                            {game.summary ?? t('games.no_summary')}
                        </p>

                        <a
                            href={game.igdb_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-5 inline-flex items-center gap-1.5 rounded-sm text-sm font-semibold text-ink-muted transition-colors hover:text-gold"
                        >
                            {t('games.view_on_igdb')}
                            <ExternalIcon className="size-4" />
                        </a>

                        {library !== null && (
                            <div className="mt-10 max-w-2xl">
                                <LibraryEntryForm
                                    // Remount after add/remove so the form resets.
                                    key={library.entry?.id ?? 'new'}
                                    gameSlug={game.slug}
                                    entry={library.entry}
                                    platforms={library.platforms}
                                />
                            </div>
                        )}
                    </div>
                </div>
            </article>
        </AppLayout>
    );
}

/** The cover lights the marquee behind it, like a backlit cabinet header. */
function Backlight({ imageId }: { imageId: string }) {
    return (
        <div
            aria-hidden="true"
            className="pointer-events-none absolute -top-10 left-1/2 h-[480px] w-screen -translate-x-1/2 overflow-hidden [mask-image:linear-gradient(to_bottom,black_25%,transparent)] md:-top-16"
        >
            <img
                src={igdbImageUrl(imageId, 'cover_small')}
                alt=""
                className="size-full scale-125 object-cover opacity-35 blur-3xl saturate-150"
            />
        </div>
    );
}

function Title({ game }: { game: GameDetail }) {
    return (
        <h1 className="font-marquee text-[1.6rem] leading-[1.05] break-words [font-stretch:100%] sm:text-4xl sm:[font-stretch:125%] lg:text-5xl">
            {game.name}
            {game.year !== null && (
                <span className="ml-3 align-baseline font-score text-xl text-ink-faint sm:text-2xl">
                    {game.year}
                </span>
            )}
        </h1>
    );
}

/** What the visitor already logged, shown as the cabinet's score. */
function EntrySummary({ entry }: { entry: LibraryEntry }) {
    return (
        <div className="mt-4 flex flex-wrap items-center gap-4">
            <StatusBadge status={entry.status} />
            {entry.rating !== null && (
                <Score
                    rating={entry.rating}
                    status={entry.status}
                    className="text-4xl sm:text-5xl"
                />
            )}
        </div>
    );
}

function Facts({ game }: { game: GameDetail }) {
    const { t } = useTranslation();
    const facts: { label: string; value: string }[] = [
        {
            label: t('games.released'),
            value:
                game.release_date !== null
                    ? formatDate(game.release_date, { dateStyle: 'long' })
                    : t('games.release_unknown'),
        },
    ];

    if (game.platforms.length > 0) {
        facts.push({
            label: t('games.platforms'),
            value: game.platforms.map((platform) => platform.name).join(', '),
        });
    }

    if (game.genres.length > 0) {
        facts.push({ label: t('games.genres'), value: game.genres.join(', ') });
    }

    return (
        <dl className="mt-7 grid gap-x-8 gap-y-4 border-y border-rule py-5 sm:grid-cols-[auto_1fr]">
            {facts.map((fact) => (
                <div key={fact.label} className="space-y-1 sm:contents">
                    <dt className="font-label text-xs text-ink-faint sm:pt-0.5">
                        {fact.label}
                    </dt>
                    <dd className="text-[15px]">{fact.value}</dd>
                </div>
            ))}
        </dl>
    );
}
