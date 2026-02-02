<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_galeria', function (Blueprint $table) {
            $table->bigIncrements('gal_id');

            $table->unsignedBigInteger('ins_id');
            $table->string('gal_url', 500);

            $table->dateTime('gal_date')->useCurrent();
            $table->unsignedTinyInteger('gal_status')->default(0);
            $table->unsignedTinyInteger('gal_ativo')->default(1);

            $table->integer('gal_rotate')->nullable();
            $table->dateTime('gal_date_status')->nullable();

            $table->char('gal_atualizado', 1)->default('S');
            $table->char('gal_local', 1)->default('N');

            $table->index(['ins_id', 'gal_ativo', 'gal_date'], 'idx_galeria_ins_ativo_date');
            $table->index(['ins_id', 'gal_date'], 'idx_galeria_ins_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_galeria');
    }
};
