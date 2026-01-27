<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            $table->string('perm_label', 120)->nullable()->after('name');
            $table->string('perm_group', 60)->nullable()->after('perm_label');
            $table->string('perm_desc', 200)->nullable()->after('perm_group');
        });
    }

    public function down(): void
    {
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            $table->dropColumn(['perm_label', 'perm_group', 'perm_desc']);
        });
    }
};
