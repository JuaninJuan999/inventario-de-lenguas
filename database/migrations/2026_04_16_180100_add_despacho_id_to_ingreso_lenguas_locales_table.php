<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->foreignId('despacho_id')->nullable()->after('despachado_at')->constrained('despachos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ingreso_lenguas_locales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('despacho_id');
        });
    }
};
