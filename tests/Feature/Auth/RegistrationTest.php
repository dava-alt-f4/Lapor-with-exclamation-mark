<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'country' => 'Indonesia',
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
