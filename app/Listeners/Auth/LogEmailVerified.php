<?php

namespace App\Listeners\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;

class LogEmailVerified
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        Log::info('User email verified', [
            'user_id' => $user->id,
            'email' => $user->email,
            'verified_at' => $user->email_verified_at,
        ]);
    }
}
