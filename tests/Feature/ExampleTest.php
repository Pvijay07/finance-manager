<?php

test('the application returns a successful response', function () {
    $response = $this->get('/manager/login');

    $response->assertStatus(200);
});
