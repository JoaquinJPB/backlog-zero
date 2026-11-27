import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { update } from '@/actions/App/Http/Controllers/Settings/ProfileController';
import AppLayout from '@/components/app-layout';
import UsernameField from '@/components/username-field';
import { useTranslation } from '@/lib/i18n';

interface Profile {
    name: string;
    username: string;
    email: string;
}

interface ProfileRules {
    name_max: number;
    username: { min: number; max: number };
}

interface ProfileSettingsProps {
    profile: Profile;
    rules: ProfileRules;
}

const inputClass =
    'mt-1 w-full rounded-md border border-stone-300 bg-transparent px-3 py-2 outline-none focus:ring-2 focus:ring-stone-400 dark:border-stone-700';

export default function ProfileSettings({
    profile,
    rules,
}: ProfileSettingsProps) {
    const { t } = useTranslation();
    const form = useForm({ name: profile.name, username: profile.username });
    const usernameChanged = form.data.username !== profile.username;

    function submit(event: FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.submit(update(), {
            preserveScroll: true,
            onSuccess: () => form.setDefaults(),
        });
    }

    return (
        <AppLayout>
            <Head title={t('settings.title')} />
            <section className="mx-auto max-w-md">
                <h1 className="text-2xl font-semibold tracking-tight">
                    {t('settings.title')}
                </h1>

                <form onSubmit={submit} className="mt-8 space-y-6">
                    <div>
                        <label
                            htmlFor="name"
                            className="block text-sm font-medium"
                        >
                            {t('settings.name_label')}
                        </label>
                        <input
                            id="name"
                            name="name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            maxLength={rules.name_max}
                            autoComplete="name"
                            required
                            aria-invalid={form.errors.name !== undefined}
                            className={inputClass}
                        />
                        <p className="mt-1 text-sm text-stone-500 dark:text-stone-400">
                            {form.errors.name ??
                                t('settings.name_hint', {
                                    max: rules.name_max,
                                })}
                        </p>
                    </div>

                    <div>
                        <UsernameField
                            value={form.data.username}
                            onChange={(value) =>
                                form.setData('username', value)
                            }
                            error={form.errors.username}
                            rules={rules.username}
                        />
                        {usernameChanged && (
                            <p className="mt-2 text-sm text-amber-700 dark:text-amber-400">
                                {t('settings.username_warning')}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="email"
                            className="block text-sm font-medium"
                        >
                            {t('settings.email_label')}
                        </label>
                        <input
                            id="email"
                            value={profile.email}
                            readOnly
                            className={`${inputClass} text-stone-500`}
                        />
                        <p className="mt-1 text-sm text-stone-500 dark:text-stone-400">
                            {t('settings.email_hint')}
                        </p>
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing || !form.isDirty}
                        className="w-full rounded-md bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-300"
                    >
                        {t('settings.submit')}
                    </button>
                </form>
            </section>
        </AppLayout>
    );
}
