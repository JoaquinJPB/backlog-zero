import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from '@/components/icons';
import { useTranslation } from '@/lib/i18n';
import { buttonClass } from '@/lib/ui';

// Mirrors the `pagination` prop built by paginated controllers.
export interface Pagination {
    current_page: number;
    last_page: number;
    prev_url: string | null;
    next_url: string | null;
}

// Phones get icon-only buttons so the row fits at 320px.
const linkClass = buttonClass('secondary', 'sm', 'max-sm:w-10 max-sm:px-0');

export default function PaginationNav({
    pagination,
}: {
    pagination: Pagination;
}) {
    const { t } = useTranslation();

    if (pagination.last_page <= 1) {
        return null;
    }

    return (
        <nav className="mt-10 flex items-center justify-between gap-4 border-t border-rule pt-6">
            {pagination.prev_url !== null ? (
                <Link
                    href={pagination.prev_url}
                    className={linkClass}
                    aria-label={t('library.previous_page')}
                >
                    <ChevronLeftIcon className="size-4" />
                    <span className="max-sm:hidden">
                        {t('library.previous_page')}
                    </span>
                </Link>
            ) : (
                <span />
            )}
            <span className="font-score text-sm text-ink-muted">
                {t('library.page_of', {
                    current: pagination.current_page,
                    last: pagination.last_page,
                })}
            </span>
            {pagination.next_url !== null ? (
                <Link
                    href={pagination.next_url}
                    className={linkClass}
                    aria-label={t('library.next_page')}
                >
                    <span className="max-sm:hidden">
                        {t('library.next_page')}
                    </span>
                    <ChevronRightIcon className="size-4" />
                </Link>
            ) : (
                <span />
            )}
        </nav>
    );
}
