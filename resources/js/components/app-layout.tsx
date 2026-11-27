import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    AccountControls,
    DesktopNav,
    MobileTabBar,
} from '@/components/app-nav';
import { AlertIcon, CheckIcon } from '@/components/icons';
import { useTranslation } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

interface AppLayoutProps {
    children: ReactNode;
    /** Content width: `default` for pages, `narrow` for forms. */
    width?: 'default' | 'narrow';
}

const IGDB_URL = 'https://www.igdb.com';

export default function AppLayout({
    children,
    width = 'default',
}: AppLayoutProps) {
    const { props, flash } = usePage();
    const signedIn = props.auth.user !== null;

    return (
        <div className="flex min-h-dvh flex-col overflow-x-clip bg-cabinet text-ink">
            <Marquee name={props.name} />

            <main
                className={cn(
                    'mx-auto w-full flex-1 px-4 pt-6 pb-10 sm:px-6 md:pt-10 lg:px-8',
                    width === 'narrow' ? 'max-w-xl' : 'max-w-6xl',
                )}
            >
                {flash.error !== undefined && (
                    <Flash tone="error" message={flash.error} />
                )}
                {flash.success !== undefined && (
                    <Flash tone="success" message={flash.success} />
                )}
                {children}
            </main>

            <Footer padded={signedIn} />
            <MobileTabBar />
        </div>
    );
}

/** The header: a backlit strip edged by a gold rule. */
function Marquee({ name }: { name: string }) {
    return (
        <header className="sticky top-0 z-40 border-b border-gold/55 bg-cabinet/88 pt-[env(safe-area-inset-top)] shadow-[0_10px_30px_-20px_rgb(255_207_64/0.35)] backdrop-blur-md">
            <div className="mx-auto flex h-14 max-w-6xl items-center gap-6 px-4 sm:px-6 md:h-16 lg:px-8">
                <Link
                    href={home()}
                    className="shrink-0 rounded-sm font-marquee text-[17px] leading-none tracking-[0.02em] md:text-lg"
                >
                    <Wordmark name={name} />
                </Link>
                <div className="h-full flex-1">
                    <DesktopNav />
                </div>
                <AccountControls />
            </div>
        </header>
    );
}

/** "Backlog Zero" → BACKLOG in ink, ZERO in gold. */
function Wordmark({ name }: { name: string }) {
    const [first, ...rest] = name.toUpperCase().split(' ');

    return (
        <span>
            <span className="text-ink">{first}</span>
            {rest.length > 0 && (
                <span className="text-gold">{rest.join(' ')}</span>
            )}
        </span>
    );
}

function Flash({
    tone,
    message,
}: {
    tone: 'error' | 'success';
    message: string;
}) {
    const Icon = tone === 'error' ? AlertIcon : CheckIcon;

    return (
        <p
            role={tone === 'error' ? 'alert' : 'status'}
            className={cn(
                'mb-6 flex animate-flash-in items-start gap-3 rounded-lg border px-4 py-3 text-sm font-medium',
                tone === 'error'
                    ? 'border-red/40 bg-red/10 text-[#ffb3b3]'
                    : 'border-gold/40 bg-gold/10 text-[#ffe7a0]',
            )}
        >
            <Icon
                className={cn(
                    'mt-px size-[18px] shrink-0',
                    tone === 'error' ? 'text-red' : 'text-gold',
                )}
            />
            {message}
        </p>
    );
}

function Footer({ padded }: { padded: boolean }) {
    const { t } = useTranslation();
    const [before, after] = t('footer.igdb_attribution').split(':link');

    return (
        <footer
            className={cn(
                'border-t border-rule',
                padded && 'mb-[calc(4rem+env(safe-area-inset-bottom))] md:mb-0',
            )}
        >
            <div className="mx-auto max-w-6xl px-4 py-5 text-xs text-ink-faint sm:px-6 lg:px-8">
                {before}
                <a
                    href={IGDB_URL}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-ink-muted underline transition-colors hover:text-ink"
                >
                    IGDB.com
                </a>
                {after}
            </div>
        </footer>
    );
}
