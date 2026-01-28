<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // Make nis nullable
            $table->string('nis')->nullable()->change();

            // Add user_id column for guru voting
            $table->unsignedBigInteger('user_id')->nullable()->after('calon_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Drop old unique constraint on nis
            $table->dropUnique(['nis']);

            // Add composite unique constraint or handle null values properly
            // Since we can't create a unique constraint that handles nulls well in all databases,
            // we'll use a partial index approach or rely on application logic
            // For now, add indexes for better query performance
            $table->index(['nis', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Drop index
            $table->dropIndex(['nis', 'user_id']);

            // Restore nis unique constraint
            $table->string('nis')->change();
            $table->unique('nis');
        });
    }
};
