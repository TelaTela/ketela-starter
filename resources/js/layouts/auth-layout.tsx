import { lang } from '@erag/lang-sync-inertia/react';
import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';

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
