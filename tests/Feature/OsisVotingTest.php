<?php

it('landing page osis dapat diakses', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Pemilihan Ketua OSIS');
});

it('voting page dapat diakses', function () {
    $response = $this->get('/voting');

    $response->assertStatus(200);
    $response->assertViewIs('osis.voting');
});

it('siswa dapat melakukan voting setelah verifikasi nis')->skip('Database structure required for full integration test');

it('siswa tidak bisa voting dua kali dengan nis yang sama')->skip('Database structure required for full integration test');

it('nis yang tidak terdaftar ditolak')->skip('Database structure required for full integration test');

it('siswa terbaru yang diambil jika ada data duplikat')->skip('Database structure required for full integration test');
