import { cn } from '@/lib/utils';

// Shared class recipes. Components stay plain elements (buttons, links,
// inputs); these keep their look identical across every screen.

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger';
export type ButtonSize = 'md' | 'sm';

const BUTTON_BASE =
    'inline-flex shrink-0 items-center justify-center gap-2 rounded-md font-semibold whitespace-nowrap transition-[background-color,color,border-color,transform] duration-150 ease-out select-none active:scale-[0.97] disabled:pointer-events-none disabled:opacity-45';

const BUTTON_VARIANTS: Record<ButtonVariant, string> = {
    primary:
        'bg-gold text-cabinet shadow-[0_1px_0_0_rgb(255_255_255/0.35)_inset,0_6px_16px_-8px_rgb(255_207_64/0.55)] hover:bg-[#ffd95f]',
    secondary:
        'border border-rule-strong bg-cabinet-raised text-ink hover:border-ink-faint hover:bg-cabinet-high',
    ghost: 'text-ink-muted hover:bg-cabinet-high hover:text-ink',
    danger: 'text-red hover:bg-red/10',
};

const BUTTON_SIZES: Record<ButtonSize, string> = {
    md: 'h-11 px-5 text-[15px]',
    sm: 'h-9 px-3.5 text-sm',
};

export function buttonClass(
    variant: ButtonVariant = 'primary',
    size: ButtonSize = 'md',
    className?: string,
): string {
    return cn(
        BUTTON_BASE,
        BUTTON_VARIANTS[variant],
        BUTTON_SIZES[size],
        className,
    );
}

export const fieldClass =
    'mt-1.5 h-11 w-full rounded-md border border-rule-strong bg-cabinet px-3.5 text-base text-ink transition-colors duration-150 outline-none placeholder:text-ink-faint hover:border-ink-faint focus:border-gold focus:ring-3 focus:ring-gold/20 aria-invalid:border-red read-only:text-ink-muted read-only:hover:border-rule-strong';

export const labelClass = 'block text-sm font-semibold text-ink';

export const hintClass = 'mt-1.5 text-sm text-ink-muted';

export const errorClass = 'mt-1.5 text-sm font-medium text-red';

/** Pill for tabs and filters; pass `active` for the selected one. */
export function pillClass(active: boolean, className?: string): string {
    return cn(
        'inline-flex h-9 shrink-0 items-center gap-2 rounded-full border px-3.5 text-sm font-semibold transition-colors duration-150',
        active
            ? 'border-ink bg-ink text-cabinet'
            : 'border-rule-strong text-ink-muted hover:border-ink-faint hover:text-ink',
        className,
    );
}

/** A quiet raised panel: the only container shape the app uses. */
export const panelClass = 'rounded-xl border border-rule bg-cabinet-raised/70';

export function formatDate(
    date: string,
    options: Intl.DateTimeFormatOptions = { dateStyle: 'medium' },
): string {
    return new Date(`${date}T00:00:00Z`).toLocaleDateString('es-ES', {
        ...options,
        timeZone: 'UTC',
    });
}
