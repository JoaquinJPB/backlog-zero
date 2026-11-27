import { Head } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import GameCover from '@/components/game-cover';
import { useTranslation } from '@/lib/i18n';
import LibraryEntryForm from '@/components/library-entry-form';
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

    return (
        <AppLayout>
            <Head title={game.name} />
            <article className="flex flex-col gap-8 sm:flex-row">
                <GameCover
                    imageId={game.cover_image_id}
                    name={game.name}
                    size="big"
                />

                <div className="min-w-0 flex-1">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        {game.name}
                        {game.year !== null && (
                            <span className="ml-2 font-normal text-stone-500">
                                ({game.year})
                            </span>
                        )}
                    </h1>

                    <dl className="mt-6 grid gap-4 text-sm sm:grid-cols-[auto_1fr] sm:gap-x-6">
                        <dt className="font-medium text-stone-500">
                            {t('games.released')}
                        </dt>
                        <dd>
                            {game.release_date !== null ? (
                                <time dateTime={game.release_date}>
                                    {new Date(
                                        `${game.release_date}T00:00:00Z`,
                                    ).toLocaleDateString('es-ES', {
                                        dateStyle: 'long',
                                        timeZone: 'UTC',
                                    })}
                                </time>
                            ) : (
                                t('games.release_unknown')
                            )}
                        </dd>

                        {game.platforms.length > 0 && (
                            <>
                                <dt className="font-medium text-stone-500">
                                    {t('games.platforms')}
                                </dt>
                                <dd>
                                    {game.platforms
                                        .map((platform) => platform.name)
                                        .join(', ')}
                                </dd>
                            </>
                        )}

                        {game.genres.length > 0 && (
                            <>
                                <dt className="font-medium text-stone-500">
                                    {t('games.genres')}
                                </dt>
                                <dd>{game.genres.join(', ')}</dd>
                            </>
                        )}
                    </dl>

                    <p className="mt-6 leading-relaxed whitespace-pre-line text-stone-700 dark:text-stone-300">
                        {game.summary ?? t('games.no_summary')}
                    </p>

                    <a
                        href={game.igdb_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-6 inline-block text-sm underline underline-offset-4"
                    >
                        {t('games.view_on_igdb')}
                    </a>

                    {library !== null && (
                        <div className="mt-8 max-w-xl">
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
            </article>
        </AppLayout>
    );
}
