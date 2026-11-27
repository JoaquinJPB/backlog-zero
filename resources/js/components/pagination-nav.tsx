import { Link } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';

// Mirrors the `pagination` prop built by paginated controllers.
export interface Pagination {
    current_page: number;
    last_page: number;
    prev_url: string | null;
    next_url: string | null;
}

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
        <nav className="mt-8 flex items-center justify-between text-sm">
            {pagination.prev_url !== null ? (
                <Link
                    href={pagination.prev_url}
                    className="underline underline-offset-4"
                >
                    {t('library.previous_page')}
                </Link>
            ) : (
                <span />
            )}
            <span className="text-stone-500">
                {t('library.page_of', {
                    current: pagination.current_page,
                    last: pagination.last_page,
                })}
            </span>
            {pagination.next_url !== null ? (
                <Link
                    href={pagination.next_url}
                    className="underline underline-offset-4"
                >
                    {t('library.next_page')}
                </Link>
            ) : (
                <span />
            )}
        </nav>
    );
}
