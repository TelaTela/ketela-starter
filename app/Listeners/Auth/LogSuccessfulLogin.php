<?php

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        Log::info('AUTH: User authenticated successfully', [
            'user_id' => $event->user->getAuthIdentifier(),
            'remember' => $event->remember,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
