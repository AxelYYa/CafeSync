<?php

it('sends the browser security headers on every page', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    expect($response->headers->get('Content-Security-Policy'))->toContain("script-src 'self'")->toContain("frame-ancestors 'none'");
});

it('escapes user content instead of running it', function () {
    $this->post(route('login.store'), ['login' => '<script>alert(1)</script>', 'password' => 'x']);

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

it('rejects a form without the CSRF token', function () {
    // Laravel omite la verificación CSRF cuando el entorno es «testing»; se simula producción.
    app()['env'] = 'production';

    $this->post(route('login.store'), ['login' => 'gerente', 'password' => 'x'])
        ->assertStatus(419)
        ->assertSee('La página expiró');
});
