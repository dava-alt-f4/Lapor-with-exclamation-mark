<?php

use App\Models\User;

test('address settings page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('address.edit'));

    $response->assertOk();
});

test('address information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('address.update'), [
            'country' => 'Indonesia',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bandung',
            'district' => 'Coblong',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Address updated.',
        ])
        ->assertRedirect(route('address.edit'));

    expect($user->refresh()->only(['country', 'province', 'city', 'district']))->toBe([
        'country' => 'Indonesia',
        'province' => 'Jawa Barat',
        'city' => 'Kota Bandung',
        'district' => 'Coblong',
    ]);
});

test('address information requires all fields', function () {
    $user = User::factory()->create([
        'province' => 'Jawa Barat',
        'city' => 'Kota Bandung',
        'district' => 'Coblong',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('address.edit'))
        ->patch(route('address.update'), []);

    $response
        ->assertSessionHasErrors(['country', 'province', 'city', 'district'])
        ->assertRedirect(route('address.edit'));

    expect($user->refresh()->province)->toBe('Jawa Barat');
});
