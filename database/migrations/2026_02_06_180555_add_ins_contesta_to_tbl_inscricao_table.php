<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_inscricao', function (Blueprint $table) {
            $table->text('ins_contesta')->nullable()->after('ins_motivo')
                ->comment('Contestação do inscrito (visível no admin)');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_inscricao', function (Blueprint $table) {
            $table->dropColumn('ins_contesta');
        });
    }
};
