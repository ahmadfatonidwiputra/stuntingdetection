<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Adds baseline security headers to every response.
     *
     * The CSP allows 'unsafe-inline'/'unsafe-eval' for scripts and inline
     * styles because the app currently relies on inline <script>/<style>
     * blocks, style="" attributes, and Alpine.js (which evaluates
     * expressions at runtime). Tightening this further requires moving
     * inline code to external files and switching to a nonce-based policy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com",
            "connect-src 'self'",
        ]));

        return $response;
    }
}
