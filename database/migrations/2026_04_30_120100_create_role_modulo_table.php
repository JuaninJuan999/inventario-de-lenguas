<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_modulo', function (Blueprint $table) {
            $table->id();
            $table->string('role', 64)->index();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->unique(['role', 'modulo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_modulo');
    }
};
