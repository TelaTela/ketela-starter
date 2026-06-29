<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
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

        // Assert localization
        $lang = app()->getLocale();
        $basePath = rtrim(lang_path(), '/');
        $pagesLocalePath = "{$basePath}/{$lang}/pages";

        $registerPageLocalePath = "{$pagesLocalePath}/auth/register.php";
        $registerPageLocale = file_exists($registerPageLocalePath) ? require $registerPageLocalePath : [];

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('lang', fn (AssertableInertia $page) => $page
                ->where('pages/auth/register', $registerPageLocale)
            )
        );
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
