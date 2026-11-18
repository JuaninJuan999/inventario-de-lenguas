<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decomisos_manuales', function (Blueprint $table) {
            $table->id();
            $table->string('id_producto', 64)->index();
            $table->date('fecha_registro');
            $table->string('hora_registro', 32);
            $table->string('nombre_dictamen', 255);
            $table->string('nombre_enfermedad', 255)->nullable();
            $table->foreignId('ingreso_lengua_local_id')->nullable()->constrained('ingreso_lenguas_locales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decomisos_manuales');
    }
};
