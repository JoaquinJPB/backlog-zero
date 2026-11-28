import { TrophyIcon } from '@/components/icons';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

interface CompletedBadgeProps {
    /** `icon` for list rows; `pill` spells out "100 %" (game page). */
    variant?: 'icon' | 'pill';
    className?: string;
}

/** The 100% mark: a filled gold trophy, the only trophy in the app. */
export default function CompletedBadge({
    variant = 'icon',
    className,
}: CompletedBadgeProps) {
    const { t } = useTranslation();
    const label = t('library.completed_badge');

    if (variant === 'pill') {
        return (
            <span
                title={label}
                className={cn(
                    'inline-flex h-6 items-center gap-1.5 rounded-full border border-gold/45 bg-gold/12 pr-2.5 pl-2 text-gold',
                    className,
                )}
            >
                <TrophyIcon filled className="size-3.5" />
                <span aria-hidden="true" className="font-score text-xs">
                    {t('library.completed_label')}
                </span>
                <span className="sr-only">{label}</span>
            </span>
        );
    }

    return (
        <span
            role="img"
            aria-label={label}
            title={label}
            className={cn('inline-flex text-gold', className)}
        >
            <TrophyIcon filled className="size-[18px]" />
        </span>
    );
}
