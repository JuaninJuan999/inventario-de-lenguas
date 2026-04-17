<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('empresa', 512)->nullable();
            $table->string('conductor', 255)->nullable();
            $table->string('placa', 64)->nullable();
            $table->timestamp('realizado_at');
            $table->timestamps();
            $table->index('realizado_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
