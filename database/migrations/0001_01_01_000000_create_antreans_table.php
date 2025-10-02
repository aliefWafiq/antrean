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
        Schema::create('antreans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('namaLengkap');
            $table->string('noPerkara');
            $table->enum('jenisPerkara', ['Gugatan', 'Permohonan']);
            $table->string('tiketAntrean');
            $table->time('jam_perkiraan');
            $table->date('tanggal_sidang');
            $table->enum('status', ['menunggu', 'telah di panggil'])->default('menunggu');
            $table->timestamp('updated_at');
            $table->timestamp('created_at');

            $table->unique(['tiketAntrean', 'tanggal_sidang']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antreans');
        Schema::dropIfExists('sessions');
    }
};
