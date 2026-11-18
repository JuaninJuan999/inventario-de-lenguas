<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->boolean('es_adicional')->default(false)->after('destino');
            $table->index('es_adicional');
        });
    }

    public function down(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->dropIndex(['es_adicional']);
            $table->dropColumn('es_adicional');
        });
    }
};
