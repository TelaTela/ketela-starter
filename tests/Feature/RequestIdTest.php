<?php

namespace Tests\Feature;

use App\Concerns\Testing\RefreshLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class RequestIdTest extends TestCase
{
    use RefreshLog;

    public function test_inertia_shared_props_contains_request_id(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('requestId')
            ->where('requestId', fn ($value) => Str::isUuid($value))
        );
    }

    public function test_log_context_contains_request_id(): void
    {
        Route::middleware('web')->get('/test-log', function () {
            Log::info('Test log message');

            return response('ok');
        });

        $this->get('/test-log');

        $logContent = (string) file_get_contents(storage_path('logs/test.log'));

        $this->assertStringContainsString('"request_id"', $logContent);
    }
}
