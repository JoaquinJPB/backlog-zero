import { igdbImageUrl } from '@/lib/igdb';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

type CoverSize = 'xs' | 'small' | 'big';

interface GameCoverProps {
    imageId: string | null;
    name: string;
    size?: CoverSize;
    className?: string;
    /** Above-the-fold covers load eagerly. */
    priority?: boolean;
}

// Box sizes keep IGDB's 264:374 portrait ratio. `big` is fluid: the parent
// sets the width.
const SIZES: Record<
    CoverSize,
    { igdb: 'cover_small' | 'cover_big'; box: string }
> = {
    xs: { igdb: 'cover_small', box: 'w-10 h-14' },
    small: { igdb: 'cover_small', box: 'w-14 h-20' },
    big: { igdb: 'cover_big', box: 'w-full aspect-[264/374]' },
};

const FRAME =
    'shrink-0 rounded-[5px] bg-cabinet-high shadow-[0_8px_20px_-10px_rgb(0_0_0/0.8)] ring-1 ring-white/8';

export default function GameCover({
    imageId,
    name,
    size = 'small',
    className,
    priority = false,
}: GameCoverProps) {
    const { t } = useTranslation();
    const { igdb, box } = SIZES[size];

    if (imageId === null) {
        return (
            <div
                className={cn(
                    box,
                    FRAME,
                    'flex flex-col items-center justify-center gap-1 overflow-hidden p-1 text-center',
                    className,
                )}
                role="img"
                aria-label={t('games.no_cover')}
            >
                <span
                    aria-hidden="true"
                    className={cn(
                        'font-marquee text-ink-faint',
                        size === 'big' ? 'text-5xl' : 'text-base',
                    )}
                >
                    {name.trim().charAt(0).toUpperCase() || '?'}
                </span>
                {size === 'big' && (
                    <span className="text-xs text-ink-faint">
                        {t('games.no_cover')}
                    </span>
                )}
            </div>
        );
    }

    return (
        <img
            src={igdbImageUrl(imageId, igdb)}
            srcSet={`${igdbImageUrl(imageId, igdb, true)} 2x`}
            alt={t('games.cover_alt', { name })}
            loading={priority ? 'eager' : 'lazy'}
            fetchPriority={priority ? 'high' : undefined}
            decoding="async"
            width={264}
            height={374}
            className={cn(box, FRAME, 'object-cover', className)}
        />
    );
}
