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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winery_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->date('fecha');
            $table->boolean('tipo');
            $table->string('detalles', 30)->nullable();
            $table->integer('company_id');
            $table->timestamps();
        });

        Schema::create('item_movement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('item_movement');
        Schema::dropIfExists('movements');
    }
};
