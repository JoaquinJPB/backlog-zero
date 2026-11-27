<?php

use Illuminate\Support\Facades\Route;

// In production the app sits behind Render's TLS-terminating proxy. Without
// trusting it, Laravel would build http:// URLs (e.g. the Google OAuth
// callback) and Google would reject the redirect.
test('requests forwarded as https by the proxy are treated as secure', function () {
    Route::get('/_proxy-check', fn () => request()->isSecure() ? 'secure' : 'insecure');

    $this->get('/_proxy-check', ['X-Forwarded-Proto' => 'https'])
        ->assertContent('secure');
});
