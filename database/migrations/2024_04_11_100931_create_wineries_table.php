<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wineries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('nombre', 30);
            $table->string('descripcion', 50)->nullable();
            $table->string('ubicacion', 30);
            $table->timestamps();
        });

        Schema::create('item_winery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winery_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->integer('cantidad');
            $table->date('fecha_vencimiento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_winery');
        Schema::dropIfExists('wineries');
    }
};
