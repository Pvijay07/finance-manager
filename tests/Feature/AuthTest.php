<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    // Avoid any accidental database writes by faking events
    Event::fake();
});

test('login page renders correctly for manager', function () {
    $response = $this->get('/manager/login');
    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('login page renders correctly for admin', function () {
    $response = $this->get('/admin/login');
    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('login page renders correctly for ca', function () {
    $response = $this->get('/ca/login');
    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('invalid role for login redirects or aborts', function () {
    $response = $this->get('/invalidrole/login');
    $response->assertStatus(404);
});

test('login fails with missing credentials', function () {
    $response = $this->post('/manager/login', []);
    $response->assertSessionHasErrors(['email', 'password']);
});



test('logout clears session and redirects to login', function () {
    $user = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($user)->post('/logout');
    $response->assertRedirect('/manager/login');
});
