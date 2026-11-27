import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';
import { logout } from '@/routes';
import { redirect as providerRedirect } from '@/routes/auth';
import { search } from '@/routes/games';
import { index as journalIndex } from '@/routes/journal';
import { index as libraryIndex } from '@/routes/library';
import { profile } from '@/routes/settings';

const buttonClass =
    'rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800';

export default function AuthControls() {
    const { auth, authProviders } = usePage().props;
    const { t } = useTranslation();

    if (auth.user === null) {
        return (
            <>
                {authProviders.map((provider) => (
                    // Plain <a>: OAuth needs a full-page redirect, not an Inertia visit.
                    <a
                        key={provider}
                        href={providerRedirect(provider).url}
                        className={buttonClass}
                    >
                        {t('auth.login_with', {
                            provider: t(`providers.${provider}`),
                        })}
                    </a>
                ))}
            </>
        );
    }

    return (
        <>
            <Link href={search()} className="text-sm hover:underline">
                {t('games.search.nav')}
            </Link>
            <Link href={libraryIndex()} className="text-sm hover:underline">
                {t('library.nav')}
            </Link>
            <Link href={journalIndex()} className="text-sm hover:underline">
                {t('journal.nav')}
            </Link>
            <Link
                href={profile()}
                className="flex items-center gap-2 text-sm hover:underline"
            >
                {auth.user.avatar_url !== null && (
                    <img
                        src={auth.user.avatar_url}
                        alt=""
                        referrerPolicy="no-referrer"
                        className="size-7 rounded-full"
                    />
                )}
                {auth.user.username !== null
                    ? `@${auth.user.username}`
                    : auth.user.name}
            </Link>
            <Link href={logout()} as="button" className={buttonClass}>
                {t('auth.logout')}
            </Link>
        </>
    );
}
