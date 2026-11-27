import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AuthControls from '@/components/auth-controls';
import { useTranslation } from '@/lib/i18n';
import { home } from '@/routes';

interface AppLayoutProps {
    children: ReactNode;
}

const IGDB_URL = 'https://www.igdb.com';

export default function AppLayout({ children }: AppLayoutProps) {
    const { props, flash } = usePage();
    const { name } = props;
    const { t } = useTranslation();
    const [attributionBefore, attributionAfter] = t(
        'footer.igdb_attribution',
    ).split(':link');

    return (
        <div className="flex min-h-screen flex-col bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100">
            <header className="border-b border-stone-200 dark:border-stone-800">
                <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
                    <Link
                        href={home()}
                        className="font-semibold tracking-tight"
                    >
                        {name}
                    </Link>
                    <nav className="flex items-center gap-3">
                        <AuthControls />
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-10">
                {flash.error !== undefined && (
                    <p
                        role="alert"
                        className="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"
                    >
                        {flash.error}
                    </p>
                )}
                {flash.success !== undefined && (
                    <p
                        role="status"
                        className="mb-6 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200"
                    >
                        {flash.success}
                    </p>
                )}
                {children}
            </main>

            <footer className="border-t border-stone-200 dark:border-stone-800">
                <div className="mx-auto max-w-5xl px-4 py-6 text-sm text-stone-500 dark:text-stone-400">
                    {attributionBefore}
                    <a
                        href={IGDB_URL}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="underline underline-offset-4 hover:text-stone-900 dark:hover:text-stone-100"
                    >
                        IGDB.com
                    </a>
                    {attributionAfter}
                </div>
            </footer>
        </div>
    );
}
