<?php

namespace Database\Seeders;

use App\Models\Calon;
use Illuminate\Database\Seeder;

class CalonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Calon Putra
        Calon::create([
            'nama' => 'Terang',
            'jenis_kelamin' => 'putra',
            'nomor_urut' => 1,
            'foto_url' => '/putra 1 terang.jpeg',
            'deskripsi' => 'menjadikan organisasi yang berkualitas dengan siswa-siswi  yang berkarakter SMART (Sigap, musyawarah, Adil, Religius, tertib).',
        ]);

        Calon::create([
            'nama' => 'Sultan',
            'jenis_kelamin' => 'putra',
            'nomor_urut' => 2,
            'foto_url' => '/putra 2 sultan.jpeg',
            'deskripsi' => 'Menjadikan organisasi dengan siswa-siswi yang lebih aktif, kreatif, disiplin dan bertanggungjawab.',
        ]);

        Calon::create([
            'nama' => 'Afwan',
            'jenis_kelamin' => 'putra',
            'nomor_urut' => 3,
            'foto_url' => '/putra 3 afwan.jpeg',
            'deskripsi' => 'Membentuk budaya organisasi dengan seluruh siswa dan siswi untuk taat peraturan , disiplin dan bertanggungjawab.',
        ]);

        // Calon Putri
        Calon::create([
            'nama' => 'Hayyun',
            'jenis_kelamin' => 'putri',
            'nomor_urut' => 1,
            'foto_url' => '/putri 1 hayyun.jpeg',
            'deskripsi' => 'mewujudkan OSIS SMP miftahul Huda yang berjiwa sosial , mandiri, berprestasi, dengan semangat kebersamaan dan bertanggungjawab.',
        ]);

        Calon::create([
            'nama' => 'Maulida',
            'jenis_kelamin' => 'putri',
            'nomor_urut' => 2,
            'foto_url' => '/putri 2 maulida.jpeg',
            'deskripsi' => 'menjadikan OSIS SMP miftahul Huda sebagai organisasi yang inspiratif, solid dan berkontribusi nyata untuk sekolah.',
        ]);

        Calon::create([
            'nama' => 'Usthuanah',
            'jenis_kelamin' => 'putri',
            'nomor_urut' => 3,
            'foto_url' => '/putri 3 ustu.jpeg',
            'deskripsi' => 'Menjadikan organisasi dengan siswa-siswi yang lebih aktif, kreatif, disiplin dan bertanggungjawab.',
        ]);
    }
}
