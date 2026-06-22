<?php

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Fortify;

class LogLoginLockout
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
    public function handle(Lockout $event): void
    {
        $request = $event->request;

        Log::warning('AUTH: Login rate limit exceeded', [
            'email' => $request->input(Fortify::email()),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
