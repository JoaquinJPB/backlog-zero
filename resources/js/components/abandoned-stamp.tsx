import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';

/** Abandoning never erases a game: it is struck and stamped. */
export default function AbandonedStamp({ className }: { className?: string }) {
    const { t } = useTranslation();

    return (
        <span
            className={cn(
                'inline-block shrink-0 -rotate-3 rounded-[3px] border-2 border-red/80 px-1.5 py-px font-label text-[10px] leading-tight text-red',
                className,
            )}
        >
            {t('library.statuses.abandoned')}
        </span>
    );
}

/** Title treatment that goes with the stamp. */
export const abandonedTitleClass =
    'text-ink-muted line-through decoration-red decoration-2';
