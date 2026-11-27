import { router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/Library/LibraryEntryController';
import { StarRatingInput } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import type { GameStatus, LibraryEntry, PlatformOption } from '@/types/library';
import { allowsFinishDate, GAME_STATUSES } from '@/types/library';

interface LibraryEntryFormProps {
    gameSlug: string;
    entry: LibraryEntry | null;
    platforms: PlatformOption[];
}

interface EntryFormData {
    status: GameStatus;
    rating: number | null;
    platform_id: number | null;
    started_on: string;
    finished_on: string;
}

const fieldClass =
    'mt-1 w-full rounded-md border border-stone-300 bg-transparent px-3 py-2 outline-none focus:ring-2 focus:ring-stone-400 dark:border-stone-700';

function today(): string {
    // Local date, not UTC: "today" for the person filling the form.
    const now = new Date();
    const pad = (value: number) => String(value).padStart(2, '0');

    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

export default function LibraryEntryForm({
    gameSlug,
    entry,
    platforms,
}: LibraryEntryFormProps) {
    const { t } = useTranslation();
    const form = useForm<EntryFormData>({
        status: entry?.status ?? 'backlog',
        rating: entry?.rating ?? null,
        platform_id: entry?.platform_id ?? null,
        started_on: entry?.started_on ?? '',
        finished_on: entry?.finished_on ?? '',
    });
    const showFinishDate = allowsFinishDate(form.data.status);

    function changeStatus(status: GameStatus): void {
        form.setData((data) => ({
            ...data,
            status,
            // Finishing pre-fills today; statuses without an end clear it.
            finished_on: allowsFinishDate(status)
                ? data.finished_on || (status === 'finished' ? today() : '')
                : '',
        }));
    }

    function submit(event: FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            started_on: data.started_on || null,
            finished_on: data.finished_on || null,
        }));
        form.submit(entry === null ? store(gameSlug) : update(entry.id), {
            preserveScroll: true,
        });
    }

    function remove(): void {
        if (entry !== null && window.confirm(t('library.remove_confirm'))) {
            router.visit(destroy(entry.id), { preserveScroll: true });
        }
    }

    return (
        <form
            onSubmit={submit}
            className="space-y-5 rounded-lg border border-stone-200 p-5 dark:border-stone-800"
        >
            <h2 className="text-lg font-semibold">
                {t('library.section_title')}
            </h2>

            <fieldset>
                <legend className="text-sm font-medium">
                    {t('library.status_label')}
                </legend>
                <div className="mt-2 flex flex-wrap gap-2">
                    {GAME_STATUSES.map((status) => (
                        <label
                            key={status}
                            className="cursor-pointer rounded-full border border-stone-300 px-3 py-1 text-sm has-checked:border-stone-900 has-checked:bg-stone-900 has-checked:text-white has-focus-visible:ring-2 has-focus-visible:ring-stone-400 dark:border-stone-700 dark:has-checked:border-stone-100 dark:has-checked:bg-stone-100 dark:has-checked:text-stone-900"
                        >
                            <input
                                type="radio"
                                name="status"
                                value={status}
                                checked={form.data.status === status}
                                onChange={() => changeStatus(status)}
                                className="sr-only"
                            />
                            {t(`library.statuses.${status}`)}
                        </label>
                    ))}
                </div>
                <FieldError message={form.errors.status} />
            </fieldset>

            <div>
                <span className="text-sm font-medium">
                    {t('library.rating_label')}
                </span>
                <div className="mt-1">
                    <StarRatingInput
                        value={form.data.rating}
                        onChange={(rating) => form.setData('rating', rating)}
                    />
                </div>
                <FieldError message={form.errors.rating} />
            </div>

            {platforms.length > 0 && (
                <div>
                    <label
                        htmlFor="platform_id"
                        className="text-sm font-medium"
                    >
                        {t('library.platform_label')}
                    </label>
                    <select
                        id="platform_id"
                        value={form.data.platform_id ?? ''}
                        onChange={(event) =>
                            form.setData(
                                'platform_id',
                                event.target.value === ''
                                    ? null
                                    : Number(event.target.value),
                            )
                        }
                        className={fieldClass}
                    >
                        <option value="">{t('library.platform_none')}</option>
                        {platforms.map((platform) => (
                            <option key={platform.id} value={platform.id}>
                                {platform.name}
                            </option>
                        ))}
                    </select>
                    <FieldError message={form.errors.platform_id} />
                </div>
            )}

            <div className="grid gap-4 sm:grid-cols-2">
                <div>
                    <label htmlFor="started_on" className="text-sm font-medium">
                        {t('library.started_on_label')}
                    </label>
                    <input
                        id="started_on"
                        type="date"
                        max={today()}
                        value={form.data.started_on}
                        onChange={(event) =>
                            form.setData('started_on', event.target.value)
                        }
                        className={fieldClass}
                    />
                    <FieldError message={form.errors.started_on} />
                </div>
                {showFinishDate && (
                    <div>
                        <label
                            htmlFor="finished_on"
                            className="text-sm font-medium"
                        >
                            {form.data.status === 'abandoned'
                                ? t('library.abandoned_on_label')
                                : t('library.finished_on_label')}
                        </label>
                        <input
                            id="finished_on"
                            type="date"
                            min={form.data.started_on || undefined}
                            max={today()}
                            value={form.data.finished_on}
                            onChange={(event) =>
                                form.setData('finished_on', event.target.value)
                            }
                            className={fieldClass}
                        />
                        <FieldError message={form.errors.finished_on} />
                    </div>
                )}
            </div>

            <div className="flex flex-wrap items-center justify-between gap-3">
                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-md bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-300"
                >
                    {entry === null ? t('library.add') : t('library.save')}
                </button>
                {entry !== null && (
                    <button
                        type="button"
                        onClick={remove}
                        className="text-sm text-red-700 underline underline-offset-4 dark:text-red-400"
                    >
                        {t('library.remove')}
                    </button>
                )}
            </div>
        </form>
    );
}

function FieldError({ message }: { message?: string }) {
    if (message === undefined) {
        return null;
    }

    return (
        <p className="mt-1 text-sm text-red-700 dark:text-red-400">{message}</p>
    );
}
