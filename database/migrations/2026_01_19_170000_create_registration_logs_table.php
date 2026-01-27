<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_inscricao_logs', function (Blueprint $table) {
            $table->bigIncrements('log_id');

            $table->unsignedBigInteger('ins_id');
            $table->unsignedBigInteger('eve_id');

            $table->string('actor_type', 20); // admin|attendee|system
            $table->unsignedBigInteger('actor_usu_id')->nullable();

            $table->json('changes')->nullable();

            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->foreign('ins_id')->references('ins_id')->on('tbl_inscricao')->cascadeOnDelete();
            $table->foreign('eve_id')->references('eve_id')->on('tbl_eventos')->cascadeOnDelete();
            $table->foreign('actor_usu_id')->references('usu_id')->on('tbl_usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_inscricao_logs');
    }
};
