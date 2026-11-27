import { igdbImageUrl } from '@/lib/igdb';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

type CoverSize = 'small' | 'big';

interface GameCoverProps {
    imageId: string | null;
    name: string;
    size?: CoverSize;
    className?: string;
}

const SIZES: Record<
    CoverSize,
    { igdb: 'cover_small' | 'cover_big'; box: string }
> = {
    small: { igdb: 'cover_small', box: 'w-[60px] h-[85px]' },
    big: { igdb: 'cover_big', box: 'w-[264px] h-[374px] max-w-full' },
};

export default function GameCover({
    imageId,
    name,
    size = 'small',
    className,
}: GameCoverProps) {
    const { t } = useTranslation();
    const { igdb, box } = SIZES[size];

    if (imageId === null) {
        return (
            <div
                className={cn(
                    box,
                    'flex shrink-0 items-center justify-center rounded bg-stone-200 p-1 text-center text-xs text-stone-500 dark:bg-stone-800 dark:text-stone-400',
                    className,
                )}
            >
                {t('games.no_cover')}
            </div>
        );
    }

    return (
        <img
            src={igdbImageUrl(imageId, igdb)}
            srcSet={`${igdbImageUrl(imageId, igdb, true)} 2x`}
            alt={t('games.cover_alt', { name })}
            loading="lazy"
            className={cn(box, 'shrink-0 rounded object-cover', className)}
        />
    );
}
