<?php

namespace Ridho\JustSubs\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ridho\JustSubs\JustSubs;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (JustSubs::check($request)) {
            return $next($request);
        }

        abort(403);
    }
}
