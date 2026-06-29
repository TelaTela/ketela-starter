<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Assert localization
        $lang = app()->getLocale();
        $basePath = rtrim(lang_path(), '/');
        $localePath = "{$basePath}/{$lang}";
        $pagesLocalePath = "{$localePath}/pages";
        $componentsLocalePath = "{$localePath}/components";

        $dashboardPageLocalePath = "{$pagesLocalePath}/dashboard.php";
        $dashboardPageLocale = file_exists($dashboardPageLocalePath) ? require $dashboardPageLocalePath : [];

        $timedGreetingComponentLocalePath = "{$componentsLocalePath}/timed-greeting.php";
        $timedGreetingComponentLocale = file_exists($timedGreetingComponentLocalePath) ? require $timedGreetingComponentLocalePath : [];

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('lang', fn (AssertableInertia $page) => $page
                ->where('pages/dashboard', $dashboardPageLocale)
                ->where('components/timed-greeting', $timedGreetingComponentLocale)
            )
        );
    }
}
