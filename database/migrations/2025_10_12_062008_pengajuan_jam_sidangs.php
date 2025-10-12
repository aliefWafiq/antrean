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
        Schema::create('pengajuan_jam_sidangs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_user')->constrained('antreans');
            $table->time('jam_sidang');
            $table->enum('status', ['menunggu', 'ditolak', 'diterima'])->default('menunggu');
            $table->timestamp('updated_at');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_jam_sidangs');
    }
};
