export default function TimedGreeting({
    time,
}: {
    time?: 'morning' | 'noon' | 'evening';
}) {
    if (!time) {
        const currentHour = new Date(Date.now()).getHours();

        if (currentHour >= 0 && currentHour < 12) {
            time = 'morning';
        } else if (currentHour >= 12 && currentHour < 18) {
            time = 'noon';
        } else {
            time = 'evening';
        }
    }

    let greeting: string = '';

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

    return <h1 className="text-3xl font-medium">{greeting}</h1>;
}
