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

test('forgot password page renders correctly', function () {
    $response = $this->get('/forgot-password');
    $response->assertStatus(200);
    $response->assertViewIs('auth.forgot_password');
});

test('change password requires authentication', function () {
    $response = $this->post('/change-password', [
        'current_password' => 'oldpass',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123'
    ]);
    $response->assertRedirect('/login');
});

test('change password validates current password and updates when valid', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'password' => \Illuminate\Support\Facades\Hash::make('currentpassword123')
    ]);

    // Test invalid current password
    $response = $this->actingAs($user)->postJson('/change-password', [
        'current_password' => 'wrongpassword',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123'
    ]);
    $response->assertStatus(422);

    // Test successful password change
    $responseSuccess = $this->actingAs($user)->postJson('/change-password', [
        'current_password' => 'currentpassword123',
        'password' => 'brandnewpassword123',
        'password_confirmation' => 'brandnewpassword123'
    ]);
    $responseSuccess->assertStatus(200);
    $responseSuccess->assertJson(['success' => true]);

    $user->refresh();
    expect(\Illuminate\Support\Facades\Hash::check('brandnewpassword123', $user->password))->toBeTrue();
});
