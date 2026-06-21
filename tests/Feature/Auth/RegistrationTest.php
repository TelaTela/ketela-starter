<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\Concerns\RefreshLog;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    use RefreshLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        Notification::fake();

        $email = 'test@example.com';

        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', $email)->first();

        Log::shouldReceive('info')->with('New user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
