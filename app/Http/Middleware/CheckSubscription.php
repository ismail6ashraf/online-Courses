<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()
                ->route('instructor.pricing')
                ->with('error', 'You need an active subscription.');
        }

        return $next($request);
    }
}
