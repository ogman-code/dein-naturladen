<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUser = (string) config('naturmarkt.admin.username');
        $expectedPassword = (string) config('naturmarkt.admin.password');

        abort_if($expectedUser === '' || $expectedPassword === '', 503, 'Der Adminzugang ist noch nicht konfiguriert.');

        $valid = hash_equals($expectedUser, (string) $request->getUser())
            && hash_equals($expectedPassword, (string) $request->getPassword());

        if (! $valid) {
            return response('Anmeldung erforderlich.', 401, [
                'WWW-Authenticate' => 'Basic realm="Naturmarkt Verwaltung", charset="UTF-8"',
            ]);
        }

        return $next($request);
    }
}
