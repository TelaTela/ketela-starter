import type { LangObject } from 'node_modules/@erag/lang-sync-inertia/dist/types/lang';
import type { Auth } from '@/types/auth';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            sharedId: string;
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            lang: LangObject;
            [key: string]: unknown;
        };
    }
}
