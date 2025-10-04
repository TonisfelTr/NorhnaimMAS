<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class SecureCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->setCookie(
                new Cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    true,
                    $cookie->isHttpOnly(),
                    false,
                    'None'
                )
            );
        }

        return $response;
    }
}
