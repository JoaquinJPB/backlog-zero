import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { store } from '@/actions/App/Http/Controllers/Onboarding/UsernameController';
import AppLayout from '@/components/app-layout';
import UsernameField from '@/components/username-field';
import { useTranslation } from '@/lib/i18n';
import { buttonClass, panelClass } from '@/lib/ui';
import { cn } from '@/lib/utils';

interface UsernameRules {
    min: number;
    max: number;
}

interface ChooseUsernameProps {
    suggestion: string;
    rules: UsernameRules;
}

export default function ChooseUsername({
    suggestion,
    rules,
}: ChooseUsernameProps) {
    const { t } = useTranslation();
    const form = useForm({ username: suggestion });

    function submit(event: FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.submit(store());
    }

    return (
        <AppLayout width="narrow">
            <Head title={t('onboarding.title')} />
            <h1 className="font-marquee text-4xl leading-tight">
                {t('onboarding.title')}
            </h1>
            <p className="mt-3 text-ink-muted">
                {t('onboarding.intro', { url: '' })}
                <span className="font-score text-gold">
                    /u/{form.data.username || '…'}
                </span>
            </p>

            <form
                onSubmit={submit}
                className={cn(panelClass, 'mt-8 space-y-6 p-5 sm:p-6')}
            >
                <UsernameField
                    value={form.data.username}
                    onChange={(value) => form.setData('username', value)}
                    error={form.errors.username}
                    rules={rules}
                />
                <button
                    type="submit"
                    disabled={form.processing}
                    className={buttonClass('primary', 'md', 'w-full')}
                >
                    {t('onboarding.submit')}
                </button>
            </form>
        </AppLayout>
    );
}
