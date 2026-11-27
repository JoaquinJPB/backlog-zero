import { useTranslation } from '@/lib/i18n';
import { STATUS_INK } from '@/lib/status';
import { cn } from '@/lib/utils';
import type { GameStatus } from '@/types/library';

/** 1-10 (half stars) → "4½", "5", "½". */
export function starsFraction(rating: number): string {
    const whole = Math.floor(rating / 2);
    const half = rating % 2 === 1 ? '½' : '';

    return whole === 0 ? half : `${whole}${half}`;
}

/** Averages keep one decimal: 7.3 → "3,7". */
export function starsDecimal(rating: number): string {
    return (rating / 2).toLocaleString('es-ES', { maximumFractionDigits: 1 });
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
                stars: (rating / 2).toLocaleString('es-ES'),
            })}
        >
            {starsFraction(rating)}
        </span>
    );
}
