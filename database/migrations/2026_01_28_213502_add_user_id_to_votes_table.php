<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Legacy PILKETOS schema is deliberately baselined and never altered.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op; never alter legacy or shared data.
    }
};
