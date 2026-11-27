import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { store } from '@/actions/App/Http/Controllers/Onboarding/UsernameController';
import AppLayout from '@/components/app-layout';
import UsernameField from '@/components/username-field';
import { useTranslation } from '@/lib/i18n';

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
        <AppLayout>
            <Head title={t('onboarding.title')} />
            <section className="mx-auto max-w-md">
                <h1 className="text-2xl font-semibold tracking-tight">
                    {t('onboarding.title')}
                </h1>
                <p className="mt-2 text-stone-600 dark:text-stone-400">
                    {t('onboarding.intro', {
                        url: `/u/${form.data.username || '…'}`,
                    })}
                </p>

                <form onSubmit={submit} className="mt-8 space-y-6">
                    <UsernameField
                        value={form.data.username}
                        onChange={(value) => form.setData('username', value)}
                        error={form.errors.username}
                        rules={rules}
                    />
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="w-full rounded-md bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-300"
                    >
                        {t('onboarding.submit')}
                    </button>
                </form>
            </section>
        </AppLayout>
    );
}
