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
        Schema::create('pengembangan_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip');
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete();
            $table->foreignId('id_pengembangan')->constrained('pengembangan')->cascadeOnDelete();            
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
        Schema::dropIfExists('pengembangan_pegawai');
    }
};
