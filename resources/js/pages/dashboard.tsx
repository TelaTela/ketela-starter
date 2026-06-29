import { Head, usePage } from '@inertiajs/react';
import TimedGreeting from '@/components/timed-greeting';
import { dashboard } from '@/routes';
import { lang } from '@erag/lang-sync-inertia/react';

export default function Dashboard() {
    const { auth } = usePage().props;
    const user = auth.user;

    const { __ } = lang();

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-row p-4">
                <div className="flex flex-col">
                    <TimedGreeting />
                    <h2 className="text-2xl font-medium">{__('pages/dashboard.hi_user', { user: user.name })}</h2>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
