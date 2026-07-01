<?php

namespace App\Http\Middleware\Locale\Sync;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppSidebar
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        syncLangFiles([
            'components/app-sidebar',
        ]);

        return $next($request);
    }
}
