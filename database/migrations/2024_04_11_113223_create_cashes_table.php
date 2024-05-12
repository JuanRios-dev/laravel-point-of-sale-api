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
        Schema::create('cashes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('nombre', 30);
            $table->decimal('monto', 20, 2)->default(0.00);
            $table->boolean('estado')->default(0);
            $table->timestamps();
        });

        Schema::create('registers', function (Blueprint $table){
            $table->id();
            $table->foreignId('cash_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->datetime('fecha_apertura');
            $table->datetime('fecha_cierre')->nullable();
            $table->decimal('saldo_apertura', 20, 2);
            $table->decimal('saldo_cierre', 20, 2)->nullable();
            $table->integer('user_apertura_id');
            $table->integer('user_cierre_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_movements', function (Blueprint $table){
            $table->id();
            $table->foreignId('cash_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->enum('tipo', ['deposito', 'retiro']);
            $table->decimal('monto', 20, 2);
            $table->text('detalles');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('registers');
        Schema::dropIfExists('cashes');
    }
};
