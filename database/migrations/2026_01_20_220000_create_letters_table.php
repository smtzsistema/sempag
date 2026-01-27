<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_cartas', function (Blueprint $table) {
            $table->bigIncrements('car_id');

            // evento
            $table->unsignedBigInteger('eve_id')->index();

            // descricao/titulo interno da carta
            $table->string('car_descricao', 255);

            // assunto do email
            $table->string('car_assunto', 255)->nullable();

            // HTML do corpo
            $table->longText('car_texto')->nullable();

            // categorias (agora salva múltiplas em JSON: [1,2,3])
            $table->json('cat_id')->nullable();

            // emails de cópia / responder para / cópia oculta
            $table->string('car_copiac', 255)->nullable();       // CC
            $table->string('car_responderto', 255)->nullable();  // Reply-To
            $table->string('car_copia', 255)->nullable();        // BCC (se for isso)

            // status da inscrição (S/E/R/N ou null = qualquer)
            $table->string('car_tipo', 1)->nullable()->index();

            // idioma (pt/en/es)
            $table->string('car_trad', 5)->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_cartas');
    }
};
