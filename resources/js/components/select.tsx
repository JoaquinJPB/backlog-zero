import type { SelectHTMLAttributes } from 'react';
import { ChevronDownIcon } from '@/components/icons';
import { fieldClass } from '@/lib/ui';
import { cn } from '@/lib/utils';

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    wrapperClassName?: string;
}

/** Native select (keyboard, mobile pickers) with the app's own chevron. */
export default function Select({
    className,
    wrapperClassName,
    children,
    ...props
}: SelectProps) {
    return (
        <span className={cn('relative block', wrapperClassName)}>
            <select
                {...props}
                className={cn(
                    fieldClass,
                    'cursor-pointer appearance-none pr-10',
                    className,
                )}
            >
                {children}
            </select>
            <ChevronDownIcon className="pointer-events-none absolute right-3 bottom-3 size-5 text-ink-muted" />
        </span>
    );
}
