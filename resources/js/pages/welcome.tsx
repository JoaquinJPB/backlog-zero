import { Head } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import { useTranslation } from '@/lib/i18n';

// Minimal on purpose: the welcome page gets its own redesign later.
export default function Welcome() {
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={t('welcome.title')} />
            <section className="mx-auto max-w-2xl py-12 text-center sm:py-20">
                <h1 className="font-marquee text-5xl leading-[0.95] sm:text-7xl">
                    {t('welcome.heading')}
                </h1>
                <p className="mx-auto mt-6 max-w-[52ch] text-lg text-ink-muted">
                    {t('welcome.intro')}
                </p>
            </section>
        </AppLayout>
    );
}
