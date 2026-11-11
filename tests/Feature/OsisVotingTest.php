<?php

use App\Http\Controllers\Pilketos\HasilController;
use App\Models\PilketosAuditLog;
use App\Models\PilketosHakPilih;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosSuara;
use App\Models\PilketosToken;
use App\Services\PilketosCandidateService;
use App\Services\PilketosTokenService;
use App\Services\PilketosVotingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    Schema::create('kelas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedTinyInteger('tingkat');
        $table->string('nama');
    });
    Schema::create('user_siswa', function (Blueprint $table): void {
        $table->unsignedMediumInteger('nis')->primary();
        $table->string('name');
    });
    Schema::create('siswas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedMediumInteger('nis');
        $table->foreignId('kelas_id');
        $table->string('tahun', 11);
    });
    Schema::create('biodata', function (Blueprint $table): void {
        $table->id();
        $table->unsignedMediumInteger('nis')->unique();
        $table->char('jenis_kelamin', 1);
    });
    $this->createEligibleStudent = function (int $nis = 100001, string $gender = 'L'): void {
        \Illuminate\Support\Facades\DB::table('kelas')->updateOrInsert(['id' => 1], ['tingkat' => 8, 'nama' => 'A']);
        \Illuminate\Support\Facades\DB::table('user_siswa')->insert(['nis' => $nis, 'name' => 'Siswa Uji']);
        \Illuminate\Support\Facades\DB::table('siswas')->insert(['nis' => $nis, 'kelas_id' => 1, 'tahun' => '2026 / 2027']);
        \Illuminate\Support\Facades\DB::table('biodata')->insert(['nis' => $nis, 'jenis_kelamin' => $gender]);
    };
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

it('siswa dapat melakukan voting dengan token yang valid', function (): void {
    ($this->createEligibleStudent)();
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $pasangan = PilketosPasangan::query()->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 1, 'ketua_nis' => 200001, 'wakil_nis' => 200002]);
    $tokenService = app(PilketosTokenService::class);
    PilketosToken::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kelas_id' => 1, 'token_hash' => $tokenService->hash('ABC123'), 'status' => 'tersedia']);
    PilketosHakPilih::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kategori' => 'L']);

    app(PilketosVotingService::class)->record(1, $pasangan->id);

    expect(PilketosSuara::query()->where('periode_id', $periode->id)->count())->toBe(1)
        ->and(PilketosToken::query()->findOrFail(1)->status)->toBe('digunakan')
        ->and(PilketosHakPilih::query()->firstOrFail()->status)->toBe('sudah_memilih');
});

it('siswa tidak bisa voting dua kali dengan token yang sama', function (): void {
    ($this->createEligibleStudent)();
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $pasangan = PilketosPasangan::query()->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 1, 'ketua_nis' => 200001, 'wakil_nis' => 200002]);
    PilketosToken::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kelas_id' => 1, 'token_hash' => app(PilketosTokenService::class)->hash('ABC123'), 'status' => 'tersedia']);
    PilketosHakPilih::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kategori' => 'L']);
    $voting = app(PilketosVotingService::class);
    $voting->record(1, $pasangan->id);

    expect(fn () => $voting->record(1, $pasangan->id))->toThrow(ValidationException::class)
        ->and(PilketosSuara::query()->count())->toBe(1);
});

it('token untuk nis yang tidak terdaftar ditolak', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    PilketosToken::query()->create(['periode_id' => $periode->id, 'nis' => 999999, 'kelas_id' => 1, 'token_hash' => app(PilketosTokenService::class)->hash('ABC123'), 'status' => 'tersedia']);

    $this->post(route('pilketos.verify'), ['token' => 'ABC123'])->assertSessionHasErrors('token');
});

it('token tidak kedaluwarsa berdasarkan waktu', function (): void {
    ($this->createEligibleStudent)();
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $pasangan = PilketosPasangan::query()->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 1, 'ketua_nis' => 200001, 'wakil_nis' => 200002]);
    PilketosToken::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kelas_id' => 1, 'token_hash' => app(PilketosTokenService::class)->hash('ABC123'), 'status' => 'tersedia', 'created_at' => now()->subYears(2), 'updated_at' => now()->subYears(2)]);
    PilketosHakPilih::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kategori' => 'L']);

    app(PilketosVotingService::class)->record(1, $pasangan->id);

    expect(PilketosSuara::query()->count())->toBe(1);
});

it('token yang belum digunakan dapat diganti tanpa memberi hak pilih kedua', function (): void {
    ($this->createEligibleStudent)();
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $token = PilketosToken::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kelas_id' => 1, 'token_hash' => app(PilketosTokenService::class)->hash('ABC123'), 'status' => 'tersedia']);
    PilketosHakPilih::query()->create(['periode_id' => $periode->id, 'nis' => 100001, 'kategori' => 'L']);

    $replacement = app(PilketosTokenService::class)->replaceUnusedToken($token->id, 9);

    expect($replacement)->toHaveLength(6)
        ->and($replacement)->not->toBe('ABC123')
        ->and(PilketosToken::query()->where('periode_id', $periode->id)->where('nis', 100001)->count())->toBe(1)
        ->and(PilketosAuditLog::query()->where('event', 'token_diganti')->count())->toBe(1);
});

it('pembaruan kandidat menolak siswa yang sudah menjadi kandidat lain', function (): void {
    foreach ([100001, 100002, 100003, 100004] as $nis) {
        ($this->createEligibleStudent)($nis);
    }
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'draft']);
    $service = app(PilketosCandidateService::class);
    $first = $service->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 1, 'ketua_nis' => 100001, 'wakil_nis' => 100002]);
    $second = $service->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 2, 'ketua_nis' => 100003, 'wakil_nis' => 100004]);

    expect(fn () => $service->update($second, ['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 2, 'ketua_nis' => $first->ketua_nis, 'wakil_nis' => 100004]))->toThrow(ValidationException::class);
});

it('hasil publik hanya dapat diakses ketika periode dipublikasikan', function (): void {
    $this->get(route('pilketos.hasil.public'))->assertNotFound();
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'ditutup', 'hasil_publik' => true]);
    PilketosPasangan::query()->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 1, 'ketua_nis' => 200001, 'wakil_nis' => 200002]);

    $this->get(route('pilketos.hasil.public'))->assertSuccessful()->assertSee('PILKETOS')->assertDontSee('200001');
});

it('laporan hasil dihasilkan sebagai pdf tanpa identitas pemilih', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'ditutup']);
    $pasangan = PilketosPasangan::query()->create(['periode_id' => $periode->id, 'kategori' => 'L', 'nomor_urut' => 1, 'ketua_nis' => 200001, 'wakil_nis' => 200002]);
    PilketosSuara::query()->create(['periode_id' => $periode->id, 'pasangan_id' => $pasangan->id, 'kategori' => 'L', 'ballot_uuid' => (string) str()->uuid()]);

    $response = app(HasilController::class)->pdf($periode);

    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('hasil-pilketos');
});
