<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_operador_placas', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 64);
            $table->string('operador_logistico', 255);
            $table->timestamps();

            $table->unique(['placa', 'operador_logistico'], 'despacho_operador_placas_placa_operador_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_operador_placas');
    }
};
