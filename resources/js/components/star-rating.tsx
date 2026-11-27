import { useId } from 'react';
import { starsFraction } from '@/components/score';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const MAX_RATING = 10; // stored as 1-10, shown as 0.5-5 stars
const STARS = 5;

function starsLabel(rating: number): string {
    return (rating / 2).toLocaleString('es-ES');
}

type StarFill = 'full' | 'half' | 'empty';

function StarIcon({ fill, className }: { fill: StarFill; className?: string }) {
    const id = useId();

    return (
        <svg viewBox="0 0 24 24" className={className} aria-hidden="true">
            <defs>
                <linearGradient id={id}>
                    <stop offset="50%" stopColor="currentColor" />
                    <stop offset="50%" stopColor="transparent" />
                </linearGradient>
            </defs>
            <path
                d="M12 2.6l2.85 6.05 6.6.8-4.87 4.57 1.27 6.55L12 17.3l-5.85 3.27 1.27-6.55L2.55 9.45l6.6-.8z"
                fill={
                    fill === 'full'
                        ? 'currentColor'
                        : fill === 'half'
                          ? `url(#${id})`
                          : 'transparent'
                }
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinejoin="round"
                className={cn(fill === 'empty' && 'opacity-45')}
            />
        </svg>
    );
}

function starFill(star: number, rating: number): StarFill {
    const value = rating - (star - 1) * 2;

    return value >= 2 ? 'full' : value === 1 ? 'half' : 'empty';
}

interface StarRatingDisplayProps {
    rating: number;
    className?: string;
    starClassName?: string;
}

export function StarRatingDisplay({
    rating,
    className,
    starClassName = 'size-4',
}: StarRatingDisplayProps) {
    const { t } = useTranslation();

    return (
        <span
            className={cn('inline-flex text-gold', className)}
            role="img"
            aria-label={t('library.rating_value', {
                stars: starsLabel(rating),
            })}
        >
            {Array.from({ length: STARS }, (_, index) => (
                <StarIcon
                    key={index}
                    fill={starFill(index + 1, rating)}
                    className={starClassName}
                />
            ))}
        </span>
    );
}

interface StarRatingInputProps {
    value: number | null;
    onChange: (value: number | null) => void;
}

/**
 * Ten radio buttons (half stars) laid over five stars: keyboard users get
 * arrow-key navigation for free, mouse and touch users tap the left or right
 * half. The score beside it ticks on every change (the app's signature move).
 */
export function StarRatingInput({ value, onChange }: StarRatingInputProps) {
    const { t } = useTranslation();
    const name = useId();

    return (
        <div className="flex flex-wrap items-center gap-x-5 gap-y-2">
            <div
                role="radiogroup"
                aria-label={t('library.rating_label')}
                className="relative -mx-1 flex text-gold"
            >
                {Array.from({ length: STARS }, (_, index) => (
                    <StarIcon
                        key={index}
                        fill={starFill(index + 1, value ?? 0)}
                        className="size-10 px-1 sm:size-9"
                    />
                ))}
                <div className="absolute inset-0 flex">
                    {Array.from({ length: MAX_RATING }, (_, index) => {
                        const rating = index + 1;

                        return (
                            <label
                                key={rating}
                                className="h-full flex-1 cursor-pointer"
                                title={t('library.rating_value', {
                                    stars: starsLabel(rating),
                                })}
                            >
                                <input
                                    type="radio"
                                    name={name}
                                    value={rating}
                                    checked={value === rating}
                                    onChange={() => onChange(rating)}
                                    className="peer sr-only"
                                />
                                <span className="block h-full rounded-sm peer-focus-visible:ring-2 peer-focus-visible:ring-gold" />
                                <span className="sr-only">
                                    {t('library.rating_value', {
                                        stars: starsLabel(rating),
                                    })}
                                </span>
                            </label>
                        );
                    })}
                </div>
            </div>

            <div className="flex items-baseline gap-3">
                <span
                    // Remount on change so the tick replays.
                    key={value ?? 'none'}
                    aria-hidden="true"
                    className={cn(
                        'inline-block min-w-[2.2ch] animate-score-tick font-score text-4xl',
                        value === null ? 'text-ink-faint' : 'text-gold',
                    )}
                >
                    {value === null ? '–' : starsFraction(value)}
                </span>
                {value === null ? (
                    <span className="text-sm text-ink-muted">
                        {t('library.rating_none')}
                    </span>
                ) : (
                    <button
                        type="button"
                        onClick={() => onChange(null)}
                        className="rounded-sm text-sm font-medium text-ink-muted underline decoration-rule-strong transition-colors hover:text-ink hover:decoration-ink-muted"
                    >
                        {t('library.rating_clear')}
                    </button>
                )}
            </div>
        </div>
    );
}
