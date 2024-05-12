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
            $table->decimal('total', 20, 2);
            $table->integer('company_id');
            $table->timestamps();
        });

        Schema::create('item_movement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 20, 2);
            $table->decimal('costo_total', 20, 2);
            $table->integer('lot_id')->nullable();
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
