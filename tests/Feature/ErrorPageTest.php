<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{

    /**
     * Forces a non-local/non-testing environment so the exception
     * handler's custom error branch actually runs.
     */
    protected function actingAsProduction(): void
    {
        $this->app['env'] = 'production';
    }

    public function test_unmatched_route_renders_error_page_with_request_id()
    {
        $this->actingAsProduction();

        $response = $this->get('/this-route-does-not-exist');

        $response
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error')
                ->where('status', 404)
                ->has('requestId')
            );
    }

    public function test_forbidden_response_renders_error_page()
    {
        $this->actingAsProduction();

        Route::get('/__test/forbidden', fn () => abort(403));

        $this->get('/__test/forbidden')
            ->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error')
                ->where('status', 403)
            );
    }

    public function test_server_error_renders_error_page()
    {
        $this->actingAsProduction();

        Route::get('/__test/server-error', fn () => abort(500));

        $this->get('/__test/server-error')
            ->assertServerError()
            ->assertInertia(fn (Assert $page) => $page
                ->component('error')
                ->where('status', 500)
            );
    }
}
