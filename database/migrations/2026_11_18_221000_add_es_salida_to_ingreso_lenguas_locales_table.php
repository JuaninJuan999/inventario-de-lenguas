<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->boolean('es_salida')->default(false)->after('es_adicional');
            $table->index('es_salida');
        });
    }

    public function down(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->dropIndex(['es_salida']);
            $table->dropColumn('es_salida');
        });
    }
};
