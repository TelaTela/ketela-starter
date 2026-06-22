<?php

namespace App\Listeners\Auth;

use App\Models\User;
use App\Notifications\Auth\LoginLockoutEmail;
use Illuminate\Auth\Events\Lockout;
use Laravel\Fortify\Fortify;

class SendLoginLockoutEmail
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

        $email = $request->input(Fortify::email());
        $ip = $request->ip();

        if ($email) {
            /** @var User|null $user */
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->notify(new LoginLockoutEmail($ip));
            }

            // WIP: do something else when no user found
        }
    }
}
