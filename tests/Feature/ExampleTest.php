<?php

test('the application redirects guests from the home page to the login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/panel');
    $this->get('/panel')->assertRedirect(route('login'));
});
