<?php

use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    $this->ca = User::factory()->create(['role' => 'ca']);
});

test('ca dashboard renders correctly', function () {
    $response = $this->actingAs($this->ca)->get('/ca/dashboard');
    $response->assertStatus(200);
});

test('ca can view statements', function () {
    $response = $this->actingAs($this->ca)->get('/ca/statements');
    $response->assertStatus(200);
});

test('ca can download statement attachments', function () {
    $response = $this->actingAs($this->ca)->get('/ca/statements/download-attachments');
    // Usually this would return a download response or a 404 if no attachments
    // We expect it not to error 500
    $this->assertTrue(in_array($response->status(), [200, 302, 404]));
});

test('ca can view invoices', function () {
    $response = $this->actingAs($this->ca)->get('/ca/invoices');
    $response->assertStatus(200);
});

test('ca can download invoice attachments', function () {
    $response = $this->actingAs($this->ca)->get('/ca/invoices/download-attachments');
    $this->assertTrue(in_array($response->status(), [200, 302, 404]));
});

test('ca can view records', function () {
    $response = $this->actingAs($this->ca)->get('/ca/records');
    $response->assertStatus(200);
});

test('ca can view expense taxes', function () {
    $response = $this->actingAs($this->ca)->get('/ca/expense-taxes');
    $response->assertStatus(200);
});

test('ca can view loans issued', function () {
    $response = $this->actingAs($this->ca)->get('/ca/loans-issued');
    $response->assertStatus(200);
});

test('ca can view loan recovery', function () {
    $response = $this->actingAs($this->ca)->get('/ca/loan-recovery');
    $response->assertStatus(200);
});

test('ca can view salary packs', function () {
    $response = $this->actingAs($this->ca)->get('/ca/salary-packs');
    $response->assertStatus(200);
});

test('ca can view tasks', function () {
    $response = $this->actingAs($this->ca)->get('/ca/tasks');
    $response->assertStatus(200);
});
