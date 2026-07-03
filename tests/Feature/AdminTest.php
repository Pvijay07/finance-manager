<?php

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin dashboard renders correctly', function () {
    // Mock the DB facade or let it hit memory if it uses DB
    $response = $this->actingAs($this->admin)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('admin can view companies list', function () {
    // Mock Company model if it's accessed directly
    $response = $this->actingAs($this->admin)->get('/admin/companies');
    $response->assertStatus(200);
});

test('admin cannot create company with missing data', function () {
    $response = $this->actingAs($this->admin)->post('/admin/companies', []);
    // Usually expects name, email, etc.
    $response->assertSessionHasErrors();
});

test('admin can view users list', function () {
    $response = $this->actingAs($this->admin)->get('/admin/users');
    $response->assertStatus(200);
});

test('admin cannot create user with missing data', function () {
    $response = $this->actingAs($this->admin)->post('/admin/users', []);
    $response->assertSessionHasErrors();
});

test('admin can view system settings', function () {
    $response = $this->actingAs($this->admin)->get('/admin/system-settings');
    $response->assertStatus(200);
});

test('admin can view expense types', function () {
    $response = $this->actingAs($this->admin)->get('/admin/expensetypes');
    $response->assertStatus(200);
});

test('admin cannot create expense type with missing data', function () {
    $response = $this->actingAs($this->admin)->post('/admin/expensetypes', []);
    $response->assertSessionHasErrors();
});
