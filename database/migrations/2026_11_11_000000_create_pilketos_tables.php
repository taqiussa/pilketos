<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilketos_periodes', function (Blueprint $table): void {
            $table->id();
            $table->string('tahun', 11)->unique();
            $table->string('nama', 150);
            $table->enum('status', ['draft', 'aktif', 'ditutup'])->default('draft')->index();
            $table->timestamp('voting_mulai')->nullable();
            $table->timestamp('voting_selesai')->nullable();
            $table->boolean('hasil_publik')->default(false);
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('judul_landing', 150)->nullable();
            $table->text('deskripsi_landing')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->timestamps();
        });

        Schema::create('pilketos_pasangan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained('pilketos_periodes')->cascadeOnDelete();
            $table->enum('kategori', ['L', 'P']);
            $table->unsignedSmallInteger('nomor_urut');
            $table->unsignedMediumInteger('ketua_nis');
            $table->unsignedMediumInteger('wakil_nis');
            $table->string('foto_ketua')->nullable();
            $table->string('foto_wakil')->nullable();
            $table->string('foto_pasangan')->nullable();
            $table->text('visi')->nullable();
            $table->longText('misi')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['periode_id', 'kategori', 'nomor_urut']);
            $table->index(['periode_id', 'ketua_nis']);
            $table->index(['periode_id', 'wakil_nis']);
        });

        Schema::create('pilketos_hak_pilih', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained('pilketos_periodes')->cascadeOnDelete();
            $table->unsignedMediumInteger('nis');
            $table->enum('kategori', ['L', 'P']);
            $table->enum('status', ['belum_memilih', 'sudah_memilih'])->default('belum_memilih')->index();
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();
            $table->unique(['periode_id', 'nis']);
            $table->index(['periode_id', 'kategori', 'status']);
        });

        Schema::create('pilketos_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained('pilketos_periodes')->cascadeOnDelete();
            $table->unsignedMediumInteger('nis');
            $table->unsignedBigInteger('kelas_id')->index();
            $table->char('token_hash', 64)->unique();
            $table->enum('status', ['tersedia', 'digunakan', 'dibatalkan'])->default('tersedia')->index();
            $table->unsignedBigInteger('generated_by')->nullable()->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->unique(['periode_id', 'nis']);
            $table->index(['periode_id', 'kelas_id', 'status']);
        });

        Schema::create('pilketos_suara', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->constrained('pilketos_periodes')->cascadeOnDelete();
            $table->foreignId('pasangan_id')->constrained('pilketos_pasangan')->restrictOnDelete();
            $table->enum('kategori', ['L', 'P']);
            $table->uuid('ballot_uuid')->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['periode_id', 'kategori']);
            $table->index(['pasangan_id']);
        });

        Schema::create('pilketos_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('periode_id')->nullable()->constrained('pilketos_periodes')->nullOnDelete();
            $table->unsignedMediumInteger('nis')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('event', 80);
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['periode_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilketos_audit_logs');
        Schema::dropIfExists('pilketos_suara');
        Schema::dropIfExists('pilketos_tokens');
        Schema::dropIfExists('pilketos_hak_pilih');
        Schema::dropIfExists('pilketos_pasangan');
        Schema::dropIfExists('pilketos_periodes');
    }
};
