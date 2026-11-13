<?php

use App\Models\PilketosHakPilih;
use App\Models\PilketosPasangan;
use App\Models\PilketosPeriode;
use App\Models\PilketosSuara;
use App\Models\PilketosToken;
use App\Models\User;
use App\Services\PilketosTeacherDirectory;
use App\Services\PilketosTokenService;
use App\Services\PilketosVotingService;
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

it('does not expose teacher administration routes to guests', function (): void {
    $this->get(route('pilketos.token-guru.index'))->assertRedirect(route('guru.login'));
    $this->get(route('pilketos.pemilih-guru.index'))->assertRedirect(route('guru.login'));
    $this->get(route('pilketos.token-guru.print', ['periode' => 1]))->assertRedirect(route('guru.login'));
    $this->get(route('pilketos.token-guru.export', ['periode' => 1]))->assertRedirect(route('guru.login'));
});

it('logs an authenticated teacher out through the existing post route', function (): void {
    $user = teacher();

    $this->actingAs($user)->post(route('guru.logout'))
        ->assertRedirect('/')
        ->assertSessionHasNoErrors();
    $this->assertGuest('web');
});

function teacher(array $attributes = []): User
{
    return User::query()->create([...['name' => 'Guru Uji', 'email' => fake()->unique()->safeEmail(), 'username' => 'guru'.fake()->unique()->numberBetween(1, 99999), 'jenis_kelamin' => 'L', 'is_active' => true, 'password' => 'password'], ...$attributes]);
}

it('includes active non admin teachers and excludes inactive or any admin role', function (): void {
    $pembina = teacher();
    $inactive = teacher(['is_active' => false]);
    $administrator = teacher();
    Role::create(['name' => 'Pembina', 'guard_name' => 'web']);
    $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $pembina->assignRole('Pembina');
    $administrator->syncRoles([$admin, Role::findByName('Pembina')]);
    $ids = app(PilketosTeacherDirectory::class)->eligibleQuery()->pluck('id');
    expect($ids)->toContain($pembina->id)->not->toContain($inactive->id)->not->toContain($administrator->id);
});

it('skips unknown gender and generates teacher token idempotently', function (): void {
    $eligible = teacher(['jenis_kelamin' => 'Perempuan']);
    teacher(['jenis_kelamin' => null]);
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $service = app(PilketosTokenService::class);
    expect($service->generateForTeachers($periode, null))->toMatchArray(['created' => 1, 'skipped' => 1]);
    expect($service->generateForTeachers($periode, null))->toMatchArray(['created' => 0, 'skipped' => 1]);
    expect(PilketosToken::query()->where('user_id', $eligible->id)->where('voter_type', 'guru')->count())->toBe(1);
});

it('uses the same six character alphabet for teacher tokens', function (): void {
    $teacher = teacher(['jenis_kelamin' => 'L']);
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    app(PilketosTokenService::class)->generateForTeachers($periode, null);
    $token = PilketosToken::query()->where('user_id', $teacher->id)->firstOrFail();

    expect($token->token_encrypted)->toMatch('/^[ABCDEFGHJKLMNPQRSTUVWXYZ123456789]{6}$/');
});

it('verifies a teacher token without storing teacher identity in the voting session', function (): void {
    $teacher = teacher(['jenis_kelamin' => 'P']);
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    app(PilketosTokenService::class)->generateForTeachers($periode, null);
    $token = PilketosToken::query()->where('user_id', $teacher->id)->firstOrFail();

    $this->post(route('pilketos.verify'), ['token' => $token->token_encrypted])
        ->assertRedirect(route('pilketos.vote'))
        ->assertSessionHas('pilketos.vote.token_id', $token->id)
        ->assertSessionHas('pilketos.vote.voter_type', 'guru')
        ->assertSessionHas('pilketos.vote.kategori', 'P')
        ->assertSessionMissing('pilketos.vote.user_id');
});

it('records one anonymous teacher ballot and rejects a duplicate', function (): void {
    $teacher = teacher(['jenis_kelamin' => 'P']);
    $periode = PilketosPeriode::query()->create(['tahun' => '2026 / 2027', 'nama' => 'PILKETOS', 'status' => 'aktif']);
    $candidate = PilketosPasangan::query()->create(['periode_id' => $periode->id, 'kategori' => 'P', 'nomor_urut' => 1, 'ketua_nis' => 100001, 'wakil_nis' => 100002, 'is_active' => true]);
    app(PilketosTokenService::class)->generateForTeachers($periode, null);
    $token = PilketosToken::query()->where('user_id', $teacher->id)->firstOrFail();
    app(PilketosVotingService::class)->record($token->id, $candidate->id);

    expect(PilketosSuara::query()->where('periode_id', $periode->id)->count())->toBe(1)
        ->and(PilketosHakPilih::query()->where('user_id', $teacher->id)->value('status'))->toBe('sudah_memilih')
        ->and($token->fresh()->status)->toBe('digunakan');
    app(PilketosVotingService::class)->record($token->id, $candidate->id);
})->throws(\Illuminate\Validation\ValidationException::class);
