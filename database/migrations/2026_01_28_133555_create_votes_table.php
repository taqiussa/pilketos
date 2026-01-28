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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('nis');
            $table->unsignedBigInteger('calon_id');
            $table->foreign('calon_id')->references('id')->on('calons')->onDelete('cascade');
            $table->timestamps();

            // Setiap siswa hanya bisa voting sekali
            $table->unique('nis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
