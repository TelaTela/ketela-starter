import { Head, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes';
import TimedGreeting from '@/components/timed-greeting';

export default function Dashboard() {
    const { auth } = usePage().props;
    const user = auth.user;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-row p-4">
                <div className="flex flex-col">
                    <TimedGreeting />
                    <h2 className="text-2xl font-medium">Hi, {user.name}</h2>
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
