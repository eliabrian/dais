<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
});

test('new users can register', function () {
    $response = $this->post(route('register'), [
        'username' => 'adventurer',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas(User::class, [
        'username' => 'adventurer',
        'email' => 'test@example.com',
    ]);
});
