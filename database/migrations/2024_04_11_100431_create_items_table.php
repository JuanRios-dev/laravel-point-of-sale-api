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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50);
            $table->string('nombre', 30);
            $table->string('descripcion', 50)->nullable();
            $table->text('imagen')->nullable();
            $table->enum('tipo', ['Inventariable', 'No Inventariable', 'Servicio']);
            $table->decimal('iva_compra', 5,2);
            $table->decimal('iva_venta', 5,2);
            $table->decimal('precio', 20,2);
            $table->string('categoria', 30)->nullable();
            $table->integer('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
