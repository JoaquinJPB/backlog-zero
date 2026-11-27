import { useTranslation } from '@/lib/i18n';
import { STATUS_INK } from '@/lib/status';
import { cn } from '@/lib/utils';
import type { GameStatus } from '@/types/library';

interface StatusBadgeProps {
    status: GameStatus;
    className?: string;
}

export default function StatusBadge({ status, className }: StatusBadgeProps) {
    const { t } = useTranslation();
    const ink = STATUS_INK[status];

    return (
        <span
            className={cn(
                'inline-flex h-6 items-center gap-1.5 rounded-full border px-2.5 text-xs font-semibold',
                ink.text,
                ink.bg,
                ink.border,
                className,
            )}
        >
            <span
                aria-hidden="true"
                className={cn('size-1.5 rounded-full', ink.dot)}
            />
            {t(`library.statuses.${status}`)}
        </span>
    );
}
