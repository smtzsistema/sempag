<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_presenca', function (Blueprint $table) {
            $table->id('pre_id');
            $table->string('ins_id', 300)->nullable()->index();
            $table->unsignedBigInteger('eve_id')->nullable()->index();

            $table->timestamp('pre_data')->nullable()->index();
            $table->string('pre_local', 255)->nullable()->index();
            $table->string('pre_tipo', 255)->nullable();
            $table->integer('pre_via')->nullable();

            $table->index(['eve_id', 'pre_local']);
            $table->index(['eve_id', 'pre_local', 'ins_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_presenca');
    }
};
