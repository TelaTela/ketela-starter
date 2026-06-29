import { lang } from '@erag/lang-sync-inertia/react';
import { Form, Head, LayoutCallback } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { createLang } from '@inertia-translator-core';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    const { __ } = lang();

    return (
        <>
            <Head title={__('pages/auth/register.page_title')} />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    {__('pages/auth/register.form.name.label')}
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder={__(
                                        'pages/auth/register.form.name.placeholder',
                                    )}
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {__('pages/auth/register.form.email.label')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder={__(
                                        'pages/auth/register.form.email.placeholder',
                                    )}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {__(
                                        'pages/auth/register.form.password.label',
                                    )}
                                </Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder={__(
                                        'pages/auth/register.form.password.placeholder',
                                    )}
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    {__(
                                        'pages/auth/register.form.password_confirmation.label',
                                    )}
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder={__(
                                        'pages/auth/register.form.password_confirmation.placeholder',
                                    )}
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={5}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                {__('pages/auth/register.form.submit.label')}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {__('pages/auth/register.have_account')}{' '}
                            <TextLink href={login()} tabIndex={6}>
                                {__('pages/auth/register.login')}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

const layoutCallback: LayoutCallback = (props) => {
    const { __ } = createLang(() => props.lang);

    return {
        title: __('pages/auth/register.layout.title'),
        description: __('pages/auth/register.layout.description'),
    };
};

Register.layout = layoutCallback;

// Register.layout = {
//     title: 'Create an account',
//     description: 'Enter your details below to create your account',
// };
