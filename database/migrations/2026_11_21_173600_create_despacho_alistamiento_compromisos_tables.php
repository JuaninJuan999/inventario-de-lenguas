<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_alistamiento_compromisos', function (Blueprint $table) {
            $table->id();
            $table->date('dia_operacion');
            $table->unsignedBigInteger('id_vehiculo_asignado');
            $table->string('placa', 64)->nullable();
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['dia_operacion', 'id_vehiculo_asignado'], 'alist_compromiso_dia_vehiculo_uq');
            $table->index('dia_operacion');
            $table->index('placa');
        });

        Schema::create('despacho_alistamiento_compromiso_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compromiso_id')
                ->constrained('despacho_alistamiento_compromisos')
                ->cascadeOnDelete();
            $table->string('id_producto', 120);
            $table->timestamps();

            $table->unique(['compromiso_id', 'id_producto'], 'alist_compromiso_item_uq');
            $table->index('id_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_alistamiento_compromiso_items');
        Schema::dropIfExists('despacho_alistamiento_compromisos');
    }
};
