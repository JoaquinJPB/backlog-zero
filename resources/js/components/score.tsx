import { useTranslation } from '@/lib/i18n';
import { STATUS_INK } from '@/lib/status';
import { cn } from '@/lib/utils';
import type { GameStatus } from '@/types/library';

/**
 * Ratings are stored 1-10 and shown as that whole number: five stars is 10,
 * four and a half is 9, one star is 2.
 */
export function formatScore(rating: number): string {
    return String(rating);
}

/** Averages (also 1-10) keep one Spanish decimal: 7.25 → "7,3". */
export function formatAverage(rating: number): string {
    return rating.toLocaleString('es-ES', { maximumFractionDigits: 1 });
}

type ScoreSize = 'sm' | 'md' | 'lg' | 'xl';

const SIZES: Record<ScoreSize, string> = {
    sm: 'text-xl',
    md: 'text-3xl',
    lg: 'text-5xl',
    xl: 'text-6xl sm:text-7xl',
};

interface ScoreProps {
    /** 1-10; null renders a dash. */
    rating: number | null;
    status?: GameStatus;
    size?: ScoreSize;
    className?: string;
}

/**
 * The signature: a rating set as an arcade score, large tabular numerals
 * in the ink of the game's status.
 */
export default function Score({
    rating,
    status = 'finished',
    size = 'md',
    className,
}: ScoreProps) {
    const { t } = useTranslation();

    if (rating === null) {
        return (
            <span
                className={cn(
                    'font-score text-ink-faint',
                    SIZES[size],
                    className,
                )}
                aria-label={t('library.rating_none')}
            >
                –
            </span>
        );
    }

    return (
        <span
            className={cn(
                'font-score',
                STATUS_INK[status].text,
                SIZES[size],
                className,
            )}
            role="img"
            aria-label={t('library.rating_value', {
                score: formatScore(rating),
            })}
        >
            {formatScore(rating)}
        </span>
    );
}
