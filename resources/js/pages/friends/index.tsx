import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/components/app-layout';
import { Avatar } from '@/components/app-nav';
import { JournalIcon, LibraryIcon, UsersIcon } from '@/components/icons';
import { useTranslation } from '@/lib/i18n';
import { show as journalOf } from '@/routes/journal';
import { show as libraryOf } from '@/routes/library';
import { show as profileOf } from '@/routes/profile';

interface Friend {
    name: string;
    username: string;
    avatar_url: string | null;
}

export default function FriendsPage({ friends }: { friends: Friend[] }) {
    const { t, tc } = useTranslation();

    return (
        <AppLayout>
            <Head title={t('friends.title')} />
            <header className="flex items-baseline justify-between gap-4">
                <h1 className="font-marquee text-[1.7rem] leading-tight sm:text-5xl">
                    {t('friends.title')}
                </h1>
                <span className="font-score text-3xl text-ink-faint sm:text-5xl">
                    {friends.length}
                </span>
            </header>
            <p className="mt-3 max-w-xl text-sm text-ink-muted">
                {t('friends.subtitle')}
            </p>

            {friends.length === 0 ? (
                <div className="mt-8 flex flex-col items-center rounded-xl border border-dashed border-rule-strong px-6 py-12 text-center">
                    <UsersIcon className="size-9 text-ink-faint" />
                    <p className="mt-4 max-w-sm text-ink-muted">
                        {t('friends.empty')}
                    </p>
                </div>
            ) : (
                <ul
                    className="mt-7 border-t border-rule"
                    aria-label={tc('friends.count', friends.length)}
                >
                    {friends.map((friend) => (
                        <FriendRow key={friend.username} friend={friend} />
                    ))}
                </ul>
            )}
        </AppLayout>
    );
}

/**
 * The row itself goes to the profile; the two buttons on the right jump
 * straight to the sections, so no menu has to be opened to pick one.
 */
function FriendRow({ friend }: { friend: Friend }) {
    const { t } = useTranslation();

    return (
        <li className="flex items-center gap-2 border-b border-rule">
            <Link
                href={profileOf(friend.username)}
                aria-label={t('friends.go_to_profile', { name: friend.name })}
                className="group -mx-3 flex min-w-0 flex-1 items-center gap-3.5 rounded-lg px-3 py-3 transition-colors duration-150 hover:bg-cabinet-raised sm:gap-4"
            >
                <Avatar
                    url={friend.avatar_url}
                    name={friend.name}
                    className="size-10 shrink-0 text-base transition-transform duration-200 ease-out group-hover:-translate-y-0.5 sm:size-11"
                />
                <span className="min-w-0">
                    <span className="block truncate leading-snug font-semibold group-hover:text-gold">
                        {friend.name}
                    </span>
                    <span className="block truncate font-score text-sm text-ink-muted">
                        @{friend.username}
                    </span>
                </span>
            </Link>
            <span className="flex shrink-0 gap-1">
                <SectionButton
                    href={journalOf(friend.username).url}
                    label={t('friends.go_to_journal', { name: friend.name })}
                    icon={JournalIcon}
                />
                <SectionButton
                    href={libraryOf(friend.username).url}
                    label={t('friends.go_to_library', { name: friend.name })}
                    icon={LibraryIcon}
                />
            </span>
        </li>
    );
}

function SectionButton({
    href,
    label,
    icon: Icon,
}: {
    href: string;
    label: string;
    icon: typeof JournalIcon;
}) {
    return (
        <Link
            href={href}
            title={label}
            className="grid size-10 place-items-center rounded-lg text-ink-muted transition-colors duration-150 hover:bg-cabinet-raised hover:text-gold"
        >
            <Icon className="size-[18px]" />
            <span className="sr-only">{label}</span>
        </Link>
    );
}
