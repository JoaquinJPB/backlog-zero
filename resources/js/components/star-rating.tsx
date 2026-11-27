import { useId } from 'react';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const MAX_RATING = 10; // stored as 1-10, shown as 0.5-5 stars
const STARS = 5;

function starsLabel(rating: number): string {
    return (rating / 2).toLocaleString('es-ES');
}

interface StarIconProps {
    fill: 'full' | 'half' | 'empty';
}

function StarIcon({ fill }: StarIconProps) {
    const id = useId();

    return (
        <svg viewBox="0 0 24 24" className="size-6" aria-hidden="true">
            <defs>
                <linearGradient id={id}>
                    <stop offset="50%" stopColor="currentColor" />
                    <stop offset="50%" stopColor="transparent" />
                </linearGradient>
            </defs>
            <path
                d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"
                fill={
                    fill === 'full'
                        ? 'currentColor'
                        : fill === 'half'
                          ? `url(#${id})`
                          : 'transparent'
                }
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function starFill(star: number, rating: number): StarIconProps['fill'] {
    const value = rating - (star - 1) * 2;

    return value >= 2 ? 'full' : value === 1 ? 'half' : 'empty';
}

interface StarRatingDisplayProps {
    rating: number;
    className?: string;
}

export function StarRatingDisplay({
    rating,
    className,
}: StarRatingDisplayProps) {
    const { t } = useTranslation();

    return (
        <span
            className={cn('inline-flex text-amber-500', className)}
            role="img"
            aria-label={t('library.rating_value', {
                stars: starsLabel(rating),
            })}
        >
            {Array.from({ length: STARS }, (_, index) => (
                <StarIcon key={index} fill={starFill(index + 1, rating)} />
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
 * arrow-key navigation for free, mouse users click the left or right half.
 */
export function StarRatingInput({ value, onChange }: StarRatingInputProps) {
    const { t } = useTranslation();
    const name = useId();

    return (
        <div className="flex items-center gap-3">
            <div
                role="radiogroup"
                aria-label={t('library.rating_label')}
                className="relative flex text-amber-500"
            >
                {Array.from({ length: STARS }, (_, index) => (
                    <StarIcon
                        key={index}
                        fill={starFill(index + 1, value ?? 0)}
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
                                <span className="block h-full rounded-sm peer-focus-visible:ring-2 peer-focus-visible:ring-stone-400" />
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
            {value === null ? (
                <span className="text-sm text-stone-500">
                    {t('library.rating_none')}
                </span>
            ) : (
                <button
                    type="button"
                    onClick={() => onChange(null)}
                    className="text-sm text-stone-500 underline underline-offset-4"
                >
                    {t('library.rating_clear')}
                </button>
            )}
        </div>
    );
}
