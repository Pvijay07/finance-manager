<?php

use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    
    // Mock Manager User
    $this->manager = User::factory()->create(['role' => 'manager']);
});

test('manager dashboard renders correctly', function () {
    $response = $this->actingAs($this->manager)->get('/manager/dashboard');
    $response->assertStatus(200);
});

test('manager can view expenses', function () {
    $response = $this->actingAs($this->manager)->get('/manager/expenses');
    $response->assertStatus(200);
});

test('manager cannot create expense with missing data', function () {
    $response = $this->actingAs($this->manager)->post('/manager/expenses', []);
    $response->assertSessionHasErrors();
});

test('manager can view standard expenses', function () {
    $response = $this->actingAs($this->manager)->get('/manager/standard-expenses');
    $response->assertStatus(200);
});

test('manager can view invoices', function () {
    $response = $this->actingAs($this->manager)->get('/manager/invoices');
    $response->assertStatus(200);
});

test('manager cannot create invoice with missing data', function () {
    $response = $this->actingAs($this->manager)->post('/manager/invoices', []);
    $response->assertSessionHasErrors();
});

test('manager can view income balances', function () {
    $response = $this->actingAs($this->manager)->get('/manager/balances');
    $response->assertStatus(200);
});

test('manager can view gst dashboard', function () {
    $response = $this->actingAs($this->manager)->get('/manager/gst');
    $response->assertStatus(200);
});

test('manager can view tds dashboard', function () {
    $response = $this->actingAs($this->manager)->get('/manager/tds');
    $response->assertStatus(200);
});

test('manager can view loans', function () {
    $response = $this->actingAs($this->manager)->get('/manager/loans');
    $response->assertStatus(200);
});

test('manager can view salary dashboard', function () {
    $response = $this->actingAs($this->manager)->get('/manager/salary/dashboard');
    $response->assertStatus(200);
});

test('manager can view reports', function () {
    $response = $this->actingAs($this->manager)->get('/manager/reports');
    $response->assertStatus(200);
});
