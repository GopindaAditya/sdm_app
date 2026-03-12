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
        Schema::create('jabatan_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jabatan')->nullable()->constrained('jabatan')->nullOnDelete();            
            $table->foreignId('id_kompetensi')->nullable()->constrained('kompetensi')->nullOnDelete();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan_kompetensi');
    }
};
