<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_ficha_campos', function (Blueprint $table) {
            $table->text('fic_validacoes')->nullable()
                ->comment('Validações padrão do preset (ex: required|string|min:3|max:120)')
                ->after('fic_opcoes');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_ficha_campos', function (Blueprint $table) {
            $table->dropColumn('fic_validacoes');
        });
    }
};
