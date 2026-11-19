<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            // true = destino editado en «Destinos y etiquetas»; la sync SIRT no lo sobrescribe.
            $table->boolean('destino_local')->default(false)->after('destino');
            $table->index('destino_local');
        });
    }

    public function down(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->dropIndex(['destino_local']);
            $table->dropColumn('destino_local');
        });
    }
};
