<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pilketos_tokens', 'pilketos_hak_pilih'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('voter_type', 10)->default('siswa')->after('periode_id');
                $blueprint->unsignedBigInteger('user_id')->nullable()->after('nis');
            });

            DB::table($table)->whereNull('voter_type')->update(['voter_type' => 'siswa']);
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique($table.'_periode_id_nis_unique');
            });
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unsignedMediumInteger('nis')->nullable()->change();
            });
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unique(['periode_id', 'nis']);
                $blueprint->unique(['periode_id', 'user_id']);
                $blueprint->index(['periode_id', 'voter_type', 'status']);
            });
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_exactly_one_voter CHECK ((voter_type = 'siswa' AND nis IS NOT NULL AND user_id IS NULL) OR (voter_type = 'guru' AND user_id IS NOT NULL AND nis IS NULL))");
            }
        }

        Schema::table('pilketos_tokens', function (Blueprint $blueprint): void {
            $blueprint->unsignedBigInteger('kelas_id')->nullable()->change();
        });

        Schema::table('pilketos_audit_logs', function (Blueprint $blueprint): void {
            $blueprint->unsignedBigInteger('user_id')->nullable()->index()->after('nis');
            $blueprint->string('voter_type', 10)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        foreach (['pilketos_tokens', 'pilketos_hak_pilih'] as $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$table}_exactly_one_voter");
            }
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['periode_id', 'voter_type', 'status']);
                $blueprint->dropUnique(['periode_id', 'nis']);
                $blueprint->dropUnique(['periode_id', 'user_id']);
                $blueprint->dropColumn(['voter_type', 'user_id']);
            });
        }

        Schema::table('pilketos_audit_logs', function (Blueprint $blueprint): void {
            $blueprint->dropColumn(['user_id', 'voter_type']);
        });
    }
};
