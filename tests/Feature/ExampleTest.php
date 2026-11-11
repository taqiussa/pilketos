<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
