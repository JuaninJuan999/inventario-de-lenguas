<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->foreignId('desposte_id')
                ->nullable()
                ->after('despacho_id')
                ->constrained('despostes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('desposte_id');
        });
    }
};
