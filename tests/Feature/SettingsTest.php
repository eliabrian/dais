<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('settings page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.edit'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings'));
});

test('user can update their avatar url', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('settings.profile.update'), [
        'avatar_url' => 'https://example.com/avatar.png',
    ]);

    $response->assertRedirect();
    expect($user->fresh()->avatar_url)->toBe('https://example.com/avatar.png');
});

test('user can toggle their theme', function () {
    $user = User::factory()->create(['theme' => 'light']);

    $response = $this->actingAs($user)->patch(route('settings.theme.update'), [
        'theme' => 'dark',
    ]);

    $response->assertRedirect();
    expect($user->fresh()->theme)->toBe('dark');
});

test('user can update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->put(route('settings.password.update'), [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertRedirect();
    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

test('user can delete their account with correct password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->delete(route('settings.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});
