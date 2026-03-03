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
        Schema::create('syarat_pelatihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jabatan')->constrained('jabatan')->cascadeOnDelete();
            $table->foreignId('id_pelatihan')->constrained('pelatihan')->cascadeOnDelete();
            $table->foreignId('id_periode')->constrained('periode')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syarat_pelatihan');
    }
};
