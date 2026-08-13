<?php

$directives = [
    "default-src 'self'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    "object-src 'none'",
    "script-src 'self' 'unsafe-inline'",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
    "font-src 'self' https://fonts.gstatic.com data:",
    "img-src 'self' https: data:",
    "connect-src 'self'",
];

if ((bool) env('FORCE_HTTPS', false)) {
    $directives[] = 'upgrade-insecure-requests';
}

return [
    'force_https' => (bool) env('FORCE_HTTPS', false),
    'content_security_policy' => implode('; ', $directives),
];
