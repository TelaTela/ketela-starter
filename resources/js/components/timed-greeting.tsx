import { lang } from '@erag/lang-sync-inertia/react';
import { useEffect, useState } from 'react';

export default function TimedGreeting({
    time,
}: {
    time?: 'morning' | 'noon' | 'evening';
}) {
    const [hour, setHour] = useState(() => new Date(Date.now()).getHours());

    useEffect(() => {
        const interval = setInterval(() => {
            setHour(new Date(Date.now()).getHours());
        }, 60 * 1000);

        return () => clearInterval(interval);
    }, []);

    if (!time) {
        if (hour >= 0 && hour < 12) {
            time = 'morning';
        } else if (hour >= 12 && hour < 18) {
            time = 'noon';
        } else {
            time = 'evening';
        }
    }

    let greeting: string = '';

    const { __ } = lang();

    switch (time) {
        case 'morning':
            greeting = 'Good Morning! 🌄';
            break;
        case 'noon':
            greeting = 'Good Afternoon! 🌞';
            break;
        case 'evening':
            greeting = 'Goon Evening! 🌛';
            break;
    }

    return <h1 className="text-3xl font-medium">{__('components/timed-greeting.' + time)}</h1>;
}
