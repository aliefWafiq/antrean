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

        Schema::create('otps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('id_user')->constrained('antreans');
            $table->integer('kodeOtp');
            $table->time('expired_at');
            $table->enum('status', ['aktif', 'expired', 'sudah ditukar'])->default('aktif');   
            $table->timestamp('updated_at');
            $table->timestamp('created_at');

            $table->unique(['kodeOtp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp');
    }
};
