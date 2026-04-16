<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insensibilizacion_id')->unique();
            $table->string('id_producto', 64)->index();
            $table->date('fecha_registro')->nullable();
            $table->string('hora_registro', 32)->nullable();
            $table->string('propietario', 512)->nullable();
            $table->text('destino')->nullable();
            $table->decimal('peso', 14, 4)->nullable();
            $table->date('fecha_turno_referencia');
            $table->timestamp('imported_at')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingreso_lenguas_locales');
    }
};
