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
        Schema::create('pengembangan_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kompetensi')->nullable()->constrained('kompetensi')->nullOnDelete();            
            $table->foreignId('id_pengembangan')->nullable()->constrained('pengembangan')->nullOnDelete();            
            $table->date('mulai_berlaku')->nullable();
            $table->date('akhir_berlaku')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembangan_kompetensi');
    }
};
