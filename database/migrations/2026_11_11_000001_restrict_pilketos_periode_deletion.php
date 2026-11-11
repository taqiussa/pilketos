<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach (['pilketos_pasangan', 'pilketos_hak_pilih', 'pilketos_tokens', 'pilketos_suara'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropForeign(['periode_id']);
                $blueprint->foreign('periode_id')->references('id')->on('pilketos_periodes')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Deliberately irreversible: restoring cascade deletion would be unsafe for election records.
    }
};
