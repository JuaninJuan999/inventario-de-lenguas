<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_vehiculo_asignado')->nullable()->after('user_id');
            $table->index(['id_vehiculo_asignado', 'realizado_at']);
        });
    }

    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->dropIndex(['id_vehiculo_asignado', 'realizado_at']);
            $table->dropColumn('id_vehiculo_asignado');
        });
    }
};
