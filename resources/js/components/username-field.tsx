import { useId } from 'react';
import { useTranslation } from '@/lib/i18n';
import { errorClass, hintClass, labelClass } from '@/lib/ui';
import { cn } from '@/lib/utils';

interface UsernameFieldProps {
    value: string;
    onChange: (value: string) => void;
    error?: string;
    rules: { min: number; max: number };
}

export default function UsernameField({
    value,
    onChange,
    error,
    rules,
}: UsernameFieldProps) {
    const { t } = useTranslation();
    const id = useId();
    const hintId = `${id}-hint`;
    const errorId = `${id}-error`;
    const invalid = error !== undefined;

    return (
        <div>
            <label htmlFor={id} className={labelClass}>
                {t('onboarding.label')}
            </label>
            <div
                className={cn(
                    'mt-1.5 flex h-11 rounded-md border bg-cabinet transition-colors duration-150 focus-within:border-gold focus-within:ring-3 focus-within:ring-gold/20 hover:border-ink-faint',
                    invalid ? 'border-red' : 'border-rule-strong',
                )}
            >
                <span className="flex items-center pl-3.5 font-score text-base text-gold select-none">
                    @
                </span>
                <input
                    id={id}
                    name="username"
                    value={value}
                    onChange={(event) =>
                        onChange(event.target.value.toLowerCase())
                    }
                    minLength={rules.min}
                    maxLength={rules.max}
                    pattern="[a-z0-9_]+"
                    autoComplete="username"
                    autoCapitalize="none"
                    spellCheck={false}
                    required
                    aria-invalid={invalid}
                    aria-describedby={invalid ? errorId : hintId}
                    className="w-full bg-transparent px-1.5 text-ink outline-none"
                />
            </div>
            {invalid ? (
                <p id={errorId} className={errorClass}>
                    {error}
                </p>
            ) : (
                <p id={hintId} className={hintClass}>
                    {t('onboarding.hint', rules)}
                </p>
            )}
        </div>
    );
}
