<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    protected string $cookieKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieKey = config('app.locale_cookie', 'locale');
    }

    public function test_uses_cookie_as_priority(): void
    {
        $response = $this->withCookie($this->cookieKey, 'id')->get('/');

        $this->assertEquals('id', App::getLocale());
    }

    public function test_uses_browser_language_when_no_cookie(): void
    {
        $this->withHeaders([
            'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
        ])->get('/');

        $this->assertEquals('id', App::getLocale());
    }

    public function test_fallback_to_default_when_no_cookie_and_no_browser_language(): void
    {
        $this->get('/');

        $this->assertEquals(config('app.locale'), App::getLocale());
    }

    public function test_stores_determined_locale_in_cookie(): void
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'id',
        ])->get('/');

        $response->assertCookie($this->cookieKey, 'id');
    }

    public function test_ignores_unsupported_locales_in_cookie(): void
    {
        Cookie::queue($this->cookieKey, 'xx');

        $this->get('/');

        $this->assertNotEquals('xx', App::getLocale());
    }
}
