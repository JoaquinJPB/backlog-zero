import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { update } from '@/actions/App/Http/Controllers/Settings/ProfileController';
import AppLayout from '@/components/app-layout';
import { AlertIcon } from '@/components/icons';
import UsernameField from '@/components/username-field';
import { useTranslation } from '@/lib/i18n';
import {
    buttonClass,
    errorClass,
    fieldClass,
    hintClass,
    labelClass,
    panelClass,
} from '@/lib/ui';
import { cn } from '@/lib/utils';

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
        <AppLayout width="narrow">
            <Head title={t('settings.title')} />
            <h1 className="font-marquee text-4xl">{t('settings.title')}</h1>

            <form
                onSubmit={submit}
                className={cn(panelClass, 'mt-8 space-y-6 p-5 sm:p-6')}
            >
                <div>
                    <label htmlFor="name" className={labelClass}>
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
                        className={fieldClass}
                    />
                    <p
                        className={
                            form.errors.name !== undefined
                                ? errorClass
                                : hintClass
                        }
                    >
                        {form.errors.name ??
                            t('settings.name_hint', { max: rules.name_max })}
                    </p>
                </div>

                <div>
                    <UsernameField
                        value={form.data.username}
                        onChange={(value) => form.setData('username', value)}
                        error={form.errors.username}
                        rules={rules.username}
                    />
                    {usernameChanged && (
                        <p className="mt-3 flex animate-rise items-start gap-2.5 rounded-lg border border-gold/35 bg-gold/8 px-3.5 py-2.5 text-sm text-[#ffe7a0]">
                            <AlertIcon className="mt-px size-4 shrink-0 text-gold" />
                            {t('settings.username_warning')}
                        </p>
                    )}
                </div>

                <div>
                    <label htmlFor="email" className={labelClass}>
                        {t('settings.email_label')}
                    </label>
                    <input
                        id="email"
                        value={profile.email}
                        readOnly
                        className={cn(fieldClass, 'cursor-default')}
                    />
                    <p className={hintClass}>{t('settings.email_hint')}</p>
                </div>

                <div className="border-t border-rule pt-5">
                    <button
                        type="submit"
                        disabled={form.processing || !form.isDirty}
                        className={buttonClass('primary', 'md', 'w-full')}
                    >
                        {t('settings.submit')}
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
