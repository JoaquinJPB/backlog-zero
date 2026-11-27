import { router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/Library/LibraryEntryController';
import Select from '@/components/select';
import { StarRatingInput } from '@/components/star-rating';
import { useTranslation } from '@/lib/i18n';
import { STATUS_INK } from '@/lib/status';
import {
    buttonClass,
    errorClass,
    fieldClass,
    labelClass,
    panelClass,
} from '@/lib/ui';
import { cn } from '@/lib/utils';
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
            className={cn(panelClass, 'space-y-7 p-5 sm:p-6')}
        >
            <h2 className="font-label text-sm text-ink-muted">
                {t('library.section_title')}
            </h2>

            <fieldset>
                <legend className={labelClass}>
                    {t('library.status_label')}
                </legend>
                <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    {GAME_STATUSES.map((status) => (
                        <StatusOption
                            key={status}
                            status={status}
                            checked={form.data.status === status}
                            onChange={() => changeStatus(status)}
                        >
                            {t(`library.statuses.${status}`)}
                        </StatusOption>
                    ))}
                </div>
                <FieldError message={form.errors.status} />
            </fieldset>

            <div>
                <span className={labelClass}>{t('library.rating_label')}</span>
                <div className="mt-2">
                    <StarRatingInput
                        value={form.data.rating}
                        onChange={(rating) => form.setData('rating', rating)}
                    />
                </div>
                <FieldError message={form.errors.rating} />
            </div>

            {platforms.length > 0 && (
                <div>
                    <label htmlFor="platform_id" className={labelClass}>
                        {t('library.platform_label')}
                    </label>
                    <Select
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
                        aria-invalid={form.errors.platform_id !== undefined}
                    >
                        <option value="">{t('library.platform_none')}</option>
                        {platforms.map((platform) => (
                            <option key={platform.id} value={platform.id}>
                                {platform.name}
                            </option>
                        ))}
                    </Select>
                    <FieldError message={form.errors.platform_id} />
                </div>
            )}

            <div className="grid gap-5 sm:grid-cols-2">
                <div>
                    <label htmlFor="started_on" className={labelClass}>
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
                        aria-invalid={form.errors.started_on !== undefined}
                        className={fieldClass}
                    />
                    <FieldError message={form.errors.started_on} />
                </div>
                {showFinishDate && (
                    <div className="animate-rise">
                        <label htmlFor="finished_on" className={labelClass}>
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
                            aria-invalid={form.errors.finished_on !== undefined}
                            className={fieldClass}
                        />
                        <FieldError message={form.errors.finished_on} />
                    </div>
                )}
            </div>

            <div className="flex flex-col-reverse gap-3 border-t border-rule pt-5 sm:flex-row sm:items-center sm:justify-between">
                {entry !== null ? (
                    <button
                        type="button"
                        onClick={remove}
                        className={buttonClass('danger', 'sm', 'self-start')}
                    >
                        {t('library.remove')}
                    </button>
                ) : (
                    <span className="hidden sm:block" />
                )}
                <button
                    type="submit"
                    disabled={form.processing}
                    className={buttonClass('primary', 'md', 'w-full sm:w-auto')}
                >
                    {entry === null ? t('library.add') : t('library.save')}
                </button>
            </div>
        </form>
    );
}

interface StatusOptionProps {
    status: GameStatus;
    checked: boolean;
    onChange: () => void;
    children: ReactNode;
}

/** A radio dressed as a key in the status ink; one is always selected. */
function StatusOption({
    status,
    checked,
    onChange,
    children,
}: StatusOptionProps) {
    const ink = STATUS_INK[status];

    return (
        <label
            className={cn(
                'flex h-12 cursor-pointer items-center gap-2.5 rounded-lg border px-3.5 text-sm font-semibold transition-[background-color,border-color,color,transform] duration-150 ease-out select-none active:scale-[0.97] has-focus-visible:ring-2 has-focus-visible:ring-gold',
                checked
                    ? cn(ink.border, ink.bg, 'text-ink')
                    : 'border-rule-strong text-ink-muted hover:border-ink-faint hover:text-ink',
            )}
        >
            <input
                type="radio"
                name="status"
                value={status}
                checked={checked}
                onChange={onChange}
                className="sr-only"
            />
            <span
                aria-hidden="true"
                className={cn(
                    'size-2.5 rounded-full transition-transform duration-200 ease-out',
                    ink.dot,
                    checked ? 'scale-100' : 'scale-75 opacity-50',
                )}
            />
            {children}
        </label>
    );
}

function FieldError({ message }: { message?: string }) {
    if (message === undefined) {
        return null;
    }

    return <p className={errorClass}>{message}</p>;
}
