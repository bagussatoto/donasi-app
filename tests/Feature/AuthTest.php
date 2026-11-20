<?php

use App\Models\User;

it('rejects registration when password confirmation does not match', function () {
    $this->post('/auth/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'berbeda123',
    ])->assertSessionHasErrors('password');

    expect(User::count())->toBe(0);
});

it('rejects registration with an invalid phone format', function () {
    $this->post('/auth/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => 'abc-tidak-valid!',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('phone');

    expect(User::count())->toBe(0);
});

it('registers a user with optional phone and uuid primary key', function () {
    $this->post('/auth/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => '+62 812-3456-7890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect('/auth/login');

    $user = User::where('email', 'budi@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->phone)->toBe('+62 812-3456-7890');
    expect($user->user_id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});
