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
        Schema::create('riwayat_pengembangan', function (Blueprint $table) {
            $table->id();
            $table->string('nip');
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete();
            $table->foreignId('id_pengembangan')->nullable()->constrained('pengembangan')->nullOnDelete();           
            $table->string('tanggal_kegiatan')->nullable();
            $table->string('sertifikat')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pengembangan');
    }
};
