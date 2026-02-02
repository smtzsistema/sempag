<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_formularios', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_formularios', 'form_foto')) {
                $table->char('form_foto', 1)
                    ->default('N')
                    ->after('form_ativo')
                    ->comment('Modulo de foto: S/N');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_formularios', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_formularios', 'form_foto')) {
                $table->dropColumn('form_foto');
            }
        });
    }
};
