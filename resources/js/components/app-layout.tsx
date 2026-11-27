import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useTranslation } from '@/lib/i18n';
import { home } from '@/routes';

interface AppLayoutProps {
    children: ReactNode;
    headerActions?: ReactNode;
}

const IGDB_URL = 'https://www.igdb.com';

export default function AppLayout({ children, headerActions }: AppLayoutProps) {
    const { name } = usePage().props;
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
                        {headerActions}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-10">
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
