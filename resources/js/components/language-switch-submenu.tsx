import { lang } from '@erag/lang-sync-inertia/react';
import { Link, usePage } from '@inertiajs/react';
import { US, ID } from 'country-flag-icons/react/3x2';
import { Check, Languages } from 'lucide-react';
import type { ReactElement } from 'react';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { switchMethod as switchLocale } from '@/routes/locale';
import { SUPPORTED_LOCALES } from '@/types/locales';
import type { SupportedLocale } from '@/types/locales';
import {
    DropdownMenuItem,
    DropdownMenuPortal,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from './ui/dropdown-menu';

function mapLocale(locale: SupportedLocale): {
    label: string;
    flag: ReactElement;
} {
    switch (locale) {
        case 'en':
            return {
                label: 'English',
                flag: <US title="English" className="mr-2" />,
            };
        case 'id':
            return {
                label: 'Bahasa Indonesia',
                flag: <ID title="Bahasa Indonesia" className="mr-2" />,
            };
    }
}

export function LanguageSwitchSubmenu() {
    const { locale } = usePage().props;
    const cleanup = useMobileNavigation();
    const { __ } = lang();

    return (
        <DropdownMenuSub>
            <DropdownMenuSubTrigger className="flex items-center gap-2 [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&_svg:not([class*='text-'])]:text-muted-foreground">
                <Languages className="mr-2" />
                {__('components/app-sidebar.user.items.switch_language')}
            </DropdownMenuSubTrigger>
            <DropdownMenuPortal>
                <DropdownMenuSubContent>
                    {SUPPORTED_LOCALES.map((locale_item, index) => {
                        const { label, flag } = mapLocale(locale_item);

                        return (
                            <DropdownMenuItem key={index} asChild>
                                <Link
                                    href={switchLocale({ locale: locale_item })}
                                    onClick={cleanup}
                                    className="flex w-full cursor-pointer items-center"
                                >
                                    {flag}

                                    {label}

                                    {locale === locale_item && (
                                        <Check className="ml-auto h-4 w-4" />
                                    )}
                                </Link>
                            </DropdownMenuItem>
                        );
                    })}
                </DropdownMenuSubContent>
            </DropdownMenuPortal>
        </DropdownMenuSub>
    );
}
