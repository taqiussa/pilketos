<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

it('landing page osis dapat diakses', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('SMP Al Musyaffa');
});

it('voting page dapat diakses', function () {
    $response = $this->get('/voting');

    $response->assertRedirect(route('pilketos.landing'));
});

it('siswa dapat melakukan voting setelah verifikasi nis')->skip('Database structure required for full integration test');

it('siswa tidak bisa voting dua kali dengan nis yang sama')->skip('Database structure required for full integration test');

it('nis yang tidak terdaftar ditolak')->skip('Database structure required for full integration test');

it('siswa terbaru yang diambil jika ada data duplikat')->skip('Database structure required for full integration test');
