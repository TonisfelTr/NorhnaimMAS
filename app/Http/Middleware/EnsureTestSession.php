<?php

namespace App\Http\Middleware;

use App\Models\PatientTestSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTestSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string)$request->route('token');

        $session = PatientTestSession::where('token', $token)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($session === null) {
            abort(410, 'Link is not valid.');
        }

        $request->attributes->add(['test_session' => $session]);

        return $next($request);
    }
}
