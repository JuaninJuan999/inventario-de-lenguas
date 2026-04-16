<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->timestamp('despachado_at')->nullable()->after('imported_at');
        });
    }

    public function down(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->dropColumn('despachado_at');
        });
    }
};
