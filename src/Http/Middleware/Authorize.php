<?php

namespace Ridho\JustSubs\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ridho\JustSubs\JustSubs;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @return Response|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (JustSubs::check($request)) {
            return $next($request);
        }

        abort(403);
    }
}
