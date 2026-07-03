import { useLang } from '@erag/lang-sync-inertia/react';
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useRef } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} ;

export default function Security({ passwordRules }: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
        const { __ } = useLang();

    return (
        <>
            <Head title={__('pages/settings/security.head_title')} />

            <h1 className="sr-only">
                {__('pages/settings/security.head_title')}
            </h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={__('pages/settings/security.heading.title')}
                    description={__(
                        'pages/settings/security.heading.description',
                    )}
                />

                <Form
                    {...SecurityController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    resetOnError={[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]}
                    resetOnSuccess
                    onError={(errors) => {
                        if (errors.password) {
                            passwordInput.current?.focus();
                        }

                        if (errors.current_password) {
                            currentPasswordInput.current?.focus();
                        }
                    }}
                    className="space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="current_password">
                                    {__(
                                        'pages/settings/security.form.current_password.label',
                                    )}
                                </Label>

                                <PasswordInput
                                    id="current_password"
                                    ref={currentPasswordInput}
                                    name="current_password"
                                    className="mt-1 block w-full"
                                    autoComplete="current-password"
                                    placeholder={__(
                                        'pages/settings/security.form.current_password.placeholder',
                                    )}
                                    aria-invalid={Boolean(
                                        errors.current_password,
                                    )}
                                    aria-describedby={
                                        errors.current_password
                                            ? 'current-password-error'
                                            : undefined
                                    }
                                />

                                <InputError id="current-password-error" message={errors.current_password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {__(
                                        'pages/settings/security.form.password.label',
                                    )}
                                </Label>

                                <PasswordInput
                                    id="password"
                                    ref={passwordInput}
                                    name="password"
                                    className="mt-1 block w-full"
                                    autoComplete="new-password"
                                    placeholder={__(
                                        'pages/settings/security.form.password.placeholder',
                                    )}
                                    passwordrules={passwordRules}
                                    aria-invalid={Boolean(errors.password)}
                                    aria-describedby={
                                        errors.password
                                            ? 'password-error'
                                            : undefined
                                    }
                                />

                                <InputError id="password-error" message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    {__(
                                        'pages/settings/security.form.password_confirmation.label',
                                    )}
                                </Label>

                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    className="mt-1 block w-full"
                                    autoComplete="new-password"
                                    placeholder={__(
                                        'pages/settings/security.form.password_confirmation.placeholder',
                                    )}
                                    passwordrules={passwordRules}
                                    aria-invalid={Boolean(
                                        errors.password_confirmation,
                                    )}
                                    aria-describedby={
                                        errors.password_confirmation
                                            ? 'password-confirmation-error'
                                            : undefined
                                    }
                                />

                                <InputError
                                    id="password-confirmation-error"
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    aria-busy={processing}
                                    data-test="update-password-button"
                                >
                                    {processing && (
                                        <LoaderCircle
                                            className="mr-2 size-4 motion-safe:animate-spin"
                                            aria-hidden="true"
                                        />
                                    )}
                                    {processing
                                        ? __(
                                              'pages/settings/security.form.save_button_processing',
                                          )
                                        : __(
                                              'pages/settings/security.form.save_button',
                                          )}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'pages/settings/security.head_title',
            href: edit(),
        },
    ],
};
