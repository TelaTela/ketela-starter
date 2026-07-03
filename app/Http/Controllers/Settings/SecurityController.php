<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Notifications\Settings\Security\PasswordUpdatedEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        syncLangFiles([
            'pages/settings/security',
        ]);

        return Inertia::render('settings/security', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Log out all other session first
        Auth::logoutOtherDevices($request->current_password);

        $user->update([
            'password' => $request->password,
        ]);

        $user->notify(new PasswordUpdatedEmail());

        Log::info('Settings/Security: Password updated.', [
            'user_id' => $user->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('settings/security.update.success')]);

        return back();
    }
}
