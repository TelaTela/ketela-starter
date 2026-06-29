import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';
import { lang } from '@erag/lang-sync-inertia/react';

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    const { __ } = lang();

    return (
        <AuthLayoutTemplate title={__(title)} description={__(description)}>
            {children}
        </AuthLayoutTemplate>
    );
}
