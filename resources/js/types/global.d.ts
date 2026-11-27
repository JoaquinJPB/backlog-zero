import type { Auth } from '@/types/auth';
import type { Translations } from '@/types/i18n';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            locale: string;
            auth: Auth;
            translations: Translations;
            [key: string]: unknown;
        };
        flashDataType: {
            error?: string;
            success?: string;
        };
    }
}
