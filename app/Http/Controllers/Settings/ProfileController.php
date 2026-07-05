<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles viewing and mutating the authenticated user's own profile
 * (name, email) from the account settings area.
 */
class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        syncLangFiles([
            'components/avatar-uploader',
            'pages/settings/profile',
        ]);

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->except('avatar'));

        $nameChanged = $user->isDirty('name');
        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        $avatarChanged = $request->hasFile('avatar');

        if ($avatarChanged) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        Log::info('Settings/Profile: Profile updated.', [
            'user_id' => $user->id,
            'name_changed' => $nameChanged,
            'email_changed' => $emailChanged,
            'avatar_changed' => $avatarChanged,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('settings/profile.update.success'),
        ]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
