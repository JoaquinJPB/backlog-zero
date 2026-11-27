import { useId } from 'react';
import { useTranslation } from '@/lib/i18n';

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

    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium">
                {t('onboarding.label')}
            </label>
            <div className="mt-1 flex rounded-md border border-stone-300 focus-within:ring-2 focus-within:ring-stone-400 dark:border-stone-700">
                <span className="flex items-center pl-3 text-stone-500 select-none">
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
                    aria-invalid={error !== undefined}
                    aria-describedby={error !== undefined ? errorId : hintId}
                    className="w-full bg-transparent px-1 py-2 outline-none"
                />
            </div>
            {error !== undefined ? (
                <p
                    id={errorId}
                    className="mt-1 text-sm text-red-700 dark:text-red-400"
                >
                    {error}
                </p>
            ) : (
                <p
                    id={hintId}
                    className="mt-1 text-sm text-stone-500 dark:text-stone-400"
                >
                    {t('onboarding.hint', rules)}
                </p>
            )}
        </div>
    );
}
