import { Link, usePage } from '@inertiajs/react';
import type { ComponentType, SVGProps } from 'react';
import {
    JournalIcon,
    LibraryIcon,
    LogOutIcon,
    SearchIcon,
    UserIcon,
} from '@/components/icons';
import { useTranslation } from '@/lib/i18n';
import { buttonClass } from '@/lib/ui';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { redirect as providerRedirect } from '@/routes/auth';
import { search } from '@/routes/games';
import { index as journalIndex } from '@/routes/journal';
import { index as libraryIndex } from '@/routes/library';
import { show as profileShow } from '@/routes/profile';
import { profile as settingsProfile } from '@/routes/settings';

interface NavItem {
    key: string;
    href: string;
    /** URL prefixes that mark this item as current. */
    match: string[];
    label: string;
    shortLabel: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
}

function useNavItems(): NavItem[] {
    const { auth } = usePage().props;
    const { t } = useTranslation();

    if (auth.user === null) {
        return [];
    }

    const profileHref =
        auth.user.username !== null
            ? profileShow(auth.user.username).url
            : settingsProfile().url;

    return [
        {
            key: 'search',
            href: search().url,
            match: ['/games'],
            label: t('games.search.nav'),
            shortLabel: t('nav.search_short'),
            icon: SearchIcon,
        },
        {
            key: 'library',
            href: libraryIndex().url,
            match: [libraryIndex().url],
            label: t('library.nav'),
            shortLabel: t('nav.library_short'),
            icon: LibraryIcon,
        },
        {
            key: 'journal',
            href: journalIndex().url,
            match: [journalIndex().url],
            label: t('journal.nav'),
            shortLabel: t('journal.nav'),
            icon: JournalIcon,
        },
        {
            key: 'profile',
            href: profileHref,
            match: ['/u/', settingsProfile().url],
            label: t('nav.profile'),
            shortLabel: t('nav.profile'),
            icon: UserIcon,
        },
    ];
}

function isCurrent(item: NavItem, url: string): boolean {
    const path = url.split('?')[0];

    return item.match.some((prefix) => path.startsWith(prefix));
}

/** Desktop: inline links in the marquee, gold rule under the current one. */
export function DesktopNav() {
    const { url } = usePage();
    const { t } = useTranslation();
    // The profile lives behind the avatar on desktop.
    const items = useNavItems().filter((item) => item.key !== 'profile');

    if (items.length === 0) {
        return null;
    }

    return (
        <nav
            aria-label={t('nav.main')}
            className="hidden h-full items-stretch gap-1 md:flex"
        >
            {items.map((item) => {
                const current = isCurrent(item, url);

                return (
                    <Link
                        key={item.key}
                        href={item.href}
                        aria-current={current ? 'page' : undefined}
                        className={cn(
                            'relative flex items-center gap-2 px-3 text-[15px] font-semibold transition-colors duration-150',
                            current
                                ? 'text-ink'
                                : 'text-ink-muted hover:text-ink',
                        )}
                    >
                        <item.icon
                            className={cn(
                                'size-[18px]',
                                current && 'text-gold',
                            )}
                        />
                        {item.label}
                        {current && (
                            <span
                                aria-hidden="true"
                                className="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-gold"
                            />
                        )}
                    </Link>
                );
            })}
        </nav>
    );
}

/** Phone: a fixed tab bar, the way installed apps do it. */
export function MobileTabBar() {
    const { url } = usePage();
    const { t } = useTranslation();
    const items = useNavItems();

    if (items.length === 0) {
        return null;
    }

    return (
        <nav
            aria-label={t('nav.main')}
            className="fixed inset-x-0 bottom-0 z-40 border-t border-rule bg-cabinet/92 pb-[env(safe-area-inset-bottom)] backdrop-blur-md md:hidden"
        >
            <ul className="mx-auto grid max-w-md grid-cols-4">
                {items.map((item) => {
                    const current = isCurrent(item, url);

                    return (
                        <li key={item.key}>
                            <Link
                                href={item.href}
                                aria-current={current ? 'page' : undefined}
                                className={cn(
                                    'relative flex h-16 flex-col items-center justify-center gap-1 text-[11px] font-semibold transition-colors duration-150 select-none active:bg-cabinet-high',
                                    current ? 'text-ink' : 'text-ink-faint',
                                )}
                            >
                                {current && (
                                    <span
                                        aria-hidden="true"
                                        className="absolute top-0 h-0.5 w-8 rounded-full bg-gold"
                                    />
                                )}
                                <item.icon
                                    className={cn(
                                        'size-6',
                                        current && 'text-gold',
                                    )}
                                />
                                {item.shortLabel}
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}

/** Right side of the marquee: sign-in for guests, avatar + logout otherwise. */
export function AccountControls() {
    const { auth, authProviders } = usePage().props;
    const { t } = useTranslation();

    if (auth.user === null) {
        return (
            <div className="flex items-center gap-2">
                {authProviders.map((provider) => (
                    // Plain <a>: OAuth needs a full-page redirect, not an Inertia visit.
                    <a
                        key={provider}
                        href={providerRedirect(provider).url}
                        className={buttonClass('primary', 'sm')}
                    >
                        {t('auth.login_with', {
                            provider: t(`providers.${provider}`),
                        })}
                    </a>
                ))}
            </div>
        );
    }

    const { user } = auth;
    const handle = user.username !== null ? `@${user.username}` : user.name;

    return (
        <div className="flex items-center gap-1">
            <Link
                href={
                    user.username !== null
                        ? profileShow(user.username)
                        : settingsProfile()
                }
                className="flex items-center gap-2.5 rounded-full py-1 pr-1 pl-1 text-sm font-semibold text-ink-muted transition-colors duration-150 hover:text-ink md:pr-3"
            >
                <Avatar url={user.avatar_url} name={user.name} />
                <span className="hidden md:inline">{handle}</span>
            </Link>
            <Link
                href={logout()}
                as="button"
                className={cn(
                    buttonClass('ghost', 'sm'),
                    'hidden size-9 px-0 md:inline-flex',
                )}
                title={t('auth.logout')}
            >
                <LogOutIcon className="size-[18px]" />
                <span className="sr-only">{t('auth.logout')}</span>
            </Link>
        </div>
    );
}

interface AvatarProps {
    url: string | null;
    name: string;
    className?: string;
}

export function Avatar({ url, name, className }: AvatarProps) {
    if (url === null) {
        return (
            <span
                aria-hidden="true"
                className={cn(
                    'grid size-8 place-items-center rounded-full bg-cabinet-high font-marquee text-sm text-gold ring-1 ring-rule-strong',
                    className,
                )}
            >
                {name.charAt(0).toUpperCase()}
            </span>
        );
    }

    return (
        <img
            src={url}
            alt=""
            referrerPolicy="no-referrer"
            className={cn(
                'size-8 rounded-full object-cover ring-1 ring-rule-strong',
                className,
            )}
        />
    );
}
