<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_organizadoras', function (Blueprint $table) {
            $table->id('org_id')->comment('Id da organizadora');

            $table->unsignedBigInteger('usu_id')->nullable()->unique()
                ->comment('Id do usuário dono (tbl_usuarios.usu_id)');
            $table->foreign('usu_id')
                ->references('usu_id')->on('tbl_usuarios')
                ->nullOnDelete();

            $table->string('org_nome')->comment('Nome da organizadora');

            // padrões Laravel (não renomear)
            $table->timestamps(); // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_organizadoras');
    }
};
