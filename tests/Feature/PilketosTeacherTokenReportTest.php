<?php

use App\Exports\PilketosTeacherTokenExport;
use App\Models\PilketosPeriode;
use App\Models\PilketosToken;
use App\Models\User;
use App\Services\PilketosTeacherTokenReportService;
use App\Services\PilketosTokenService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('username')->nullable();
        $table->string('jenis_kelamin', 20)->nullable();
        $table->boolean('is_active')->default(true);
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();
        $table->unique(['name', 'guard_name']);
    });
    Schema::create('model_has_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('role_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
        $table->primary(['role_id', 'model_id', 'model_type']);
    });
});

function reportTeacher(array $attributes = []): User
{
    return User::query()->create([...[
        'name' => 'Guru Uji',
        'email' => fake()->unique()->safeEmail(),
        'username' => 'guru'.fake()->unique()->numberBetween(1, 99999),
        'jenis_kelamin' => 'L',
        'is_active' => true,
        'password' => 'password',
    ], ...$attributes]);
}

function teacherToken(PilketosPeriode $periode, User $teacher, string $plain = '001234', string $status = 'tersedia'): PilketosToken
{
    return PilketosToken::query()->create([
        'periode_id' => $periode->id,
        'voter_type' => 'guru',
        'user_id' => $teacher->id,
        'kelas_id' => null,
        'token_hash' => app(PilketosTokenService::class)->hash($plain),
        'token_encrypted' => $plain,
        'status' => $status,
    ]);
}

it('renders the teacher token toolbar and reports all matching teachers', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $pembina = reportTeacher(['name' => 'Pembina Aktif', 'username' => 'pembina']);
    Role::create(['name' => 'Pembina', 'guard_name' => 'web']);
    $pembina->assignRole('Pembina');
    teacherToken($periode, $pembina, '001234');

    foreach (range(1, 25) as $number) {
        $teacher = reportTeacher(['name' => "Guru {$number}"]);
        teacherToken($periode, $teacher, str_pad((string) $number, 6, '0', STR_PAD_LEFT));
    }

    $this->actingAs(reportTeacher())->get(route('pilketos.token-guru.index'))
        ->assertSee('Print Token Guru')
        ->assertSee('Download Excel');

    $report = app(PilketosTeacherTokenReportService::class)->forPeriod($periode->id, 'tersedia', 'pembina');

    expect($report['rows'])->toHaveCount(1)
        ->and($report['rows'][0])->toMatchArray(['name' => 'Pembina Aktif', 'token' => '001234', 'token_label' => '001234']);
});

it('excludes inactive and admin teachers and ignores student tokens', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $eligible = reportTeacher(['name' => 'Guru Eligible']);
    $inactive = reportTeacher(['name' => 'Guru Tidak Aktif', 'is_active' => false]);
    $administrator = reportTeacher(['name' => 'Guru Admin']);
    $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $administrator->assignRole($adminRole);
    teacherToken($periode, $eligible, '000111');
    teacherToken($periode, $inactive, '000222');
    teacherToken($periode, $administrator, '000333');
    PilketosToken::query()->create([
        'periode_id' => $periode->id,
        'voter_type' => 'siswa',
        'nis' => 100001,
        'kelas_id' => 1,
        'token_hash' => app(PilketosTokenService::class)->hash('STUDENT'),
        'token_encrypted' => 'STUDENT',
        'status' => 'tersedia',
    ]);

    $report = app(PilketosTeacherTokenReportService::class)->forPeriod($periode->id);

    expect($report['rows'])->toHaveCount(1)
        ->and($report['rows'][0])->toMatchArray(['name' => 'Guru Eligible', 'token' => '000111']);
});

it('filters teacher reports by each token status without pagination', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $unmade = reportTeacher(['name' => 'Belum Dibuat']);
    $available = reportTeacher(['name' => 'Tersedia']);
    $used = reportTeacher(['name' => 'Digunakan']);
    teacherToken($periode, $available, '000444');
    teacherToken($periode, $used, '000555', 'digunakan');

    $reports = app(PilketosTeacherTokenReportService::class);

    expect($reports->forPeriod($periode->id, 'belum_dibuat')['rows'])->toHaveCount(1)
        ->and($reports->forPeriod($periode->id, 'belum_dibuat')['rows'][0]['name'])->toBe($unmade->name)
        ->and($reports->forPeriod($periode->id, 'tersedia')['rows'][0]['name'])->toBe($available->name)
        ->and($reports->forPeriod($periode->id, 'digunakan')['rows'][0]['name'])->toBe($used->name);
});

it('renders printable teacher tokens and downloads a protected xlsx without changing token status', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $teacher = reportTeacher(['name' => 'Guru Cetak', 'username' => 'cetak']);
    $token = teacherToken($periode, $teacher, '001234');
    $operator = reportTeacher();

    $this->actingAs($operator)->get(route('pilketos.token-guru.print', ['periode' => $periode->id, 'status' => 'tersedia', 'search' => 'cetak']))
        ->assertSuccessful()
        ->assertSee('DAFTAR TOKEN GURU PEMILIHAN KETUA OSIS')
        ->assertSee('Guru Cetak')
        ->assertSee('001234')
        ->assertHeaderContains('cache-control', 'private')
        ->assertHeaderContains('cache-control', 'no-store');

    $this->actingAs($operator)->get(route('pilketos.token-guru.export', ['periode' => $periode->id, 'status' => 'tersedia', 'search' => 'cetak']))
        ->assertSuccessful()
        ->assertHeaderContains('content-disposition', 'token-guru-2026-2027-tersedia.xlsx')
        ->assertHeaderContains('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertHeaderContains('cache-control', 'private')
        ->assertHeaderContains('cache-control', 'no-store');

    expect($token->fresh()->status)->toBe('tersedia')
        ->and((new PilketosTeacherTokenExport(app(PilketosTeacherTokenReportService::class)->forPeriod($periode->id)['rows']))->headings())
        ->toBe(['No', 'Nama', 'Username', 'Role', 'Token', 'Status']);
});

it('handles unavailable encrypted teacher tokens without exposing ciphertext', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $teacher = reportTeacher();
    $token = teacherToken($periode, $teacher);
    PilketosToken::query()->whereKey($token)->update(['token_encrypted' => 'invalid-ciphertext']);

    $report = app(PilketosTeacherTokenReportService::class)->forPeriod($periode->id);

    expect($report['rows'][0]['token'])->toBeNull()
        ->and($report['rows'][0]['token_label'])->toBe('Token tidak dapat ditampilkan');
});

it('shows a dash when a teacher token has no encrypted value', function (): void {
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $teacher = reportTeacher();
    $token = teacherToken($periode, $teacher);
    PilketosToken::query()->whereKey($token)->update(['token_encrypted' => null]);

    $report = app(PilketosTeacherTokenReportService::class)->forPeriod($periode->id);

    expect($report['rows'][0])->toMatchArray(['token' => null, 'token_label' => '-']);
});
