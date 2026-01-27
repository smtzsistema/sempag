<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_evento_usuarios', function (Blueprint $table) {
            $table->id('eusu_id');
            $table->unsignedBigInteger('eve_id');
            $table->unsignedBigInteger('usu_id');
            $table->timestamps();

            $table->unique(['eve_id', 'usu_id'], 'uq_evento_usuario');

            $table->foreign('eve_id')
                ->references('eve_id')
                ->on('tbl_eventos')
                ->onDelete('cascade');

            $table->foreign('usu_id')
                ->references('usu_id')
                ->on('tbl_usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_evento_usuarios');
    }
};
