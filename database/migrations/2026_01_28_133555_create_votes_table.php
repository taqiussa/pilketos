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
        // Legacy PILKETOS schema is deliberately baselined and never created.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op; never delete legacy or shared data.
    }
};
