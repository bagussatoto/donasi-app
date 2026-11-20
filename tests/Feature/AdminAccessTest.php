<?php

use App\Models\User;

it('redirects guests away from admin routes', function () {
    $this->get('/admin/categories')->assertRedirect(route('login'));
});

it('redirects non-admin users away from admin routes', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get('/admin/categories')->assertRedirect(route('login'));
});

it('requires login for profile and donation history', function () {
    $this->get('/profile')->assertRedirect(route('login'));
    $this->get('/donasi/history')->assertRedirect(route('login'));
});
