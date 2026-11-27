import { Head } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import { useTranslation } from '@/lib/i18n';

export default function Welcome() {
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={t('welcome.title')} />
            <section className="mx-auto max-w-2xl py-12 text-center">
                <h1 className="text-4xl font-semibold tracking-tight">
                    {t('welcome.heading')}
                </h1>
                <p className="mt-4 text-lg text-stone-600 dark:text-stone-400">
                    {t('welcome.intro')}
                </p>
            </section>
        </AppLayout>
    );
}
