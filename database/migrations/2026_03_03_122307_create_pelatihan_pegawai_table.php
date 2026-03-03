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
        Schema::create('pelatihan_pegawai', function (Blueprint $table) {
            $table->id();$table->string('nip', 50);
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete();
            $table->foreignId('id_pelatihan')->constrained('pelatihan')->cascadeOnDelete();
            $table->foreignId('id_periode')->constrained('periode')->cascadeOnDelete();
            $table->string('file_sertifikat');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelatihan_pegawai');
    }
};
