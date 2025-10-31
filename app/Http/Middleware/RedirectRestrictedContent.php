<?php

namespace App\Http\Middleware;

use App\Models\Story;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class RedirectRestrictedContent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            // Check if the request is for a specific story
            if ($request->route('story')) {
                $story = $request->route('story');

                if ($story instanceof Story) {
                    $guestAgeLimit = Config::get('age_rating.guest_limit_years', 16);

                    if (is_null($story->age_rating_effective_value) || $story->age_rating_effective_value >= $guestAgeLimit) {
                        // Store the intended URL before redirecting to login
                        return redirect()->route('login', ['next' => $request->fullUrl()]);
                    }
                }
            }
        }

        return $next($request);
    }
}
