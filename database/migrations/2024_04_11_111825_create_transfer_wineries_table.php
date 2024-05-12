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
        Schema::create('transfer_wineries', function (Blueprint $table) {
            $table->id();
            $table->integer('winery_origen_id');
            $table->integer('winery_destino_id');
            $table->date('fecha');
            $table->string('detalles', 30)->nullable();
            $table->integer('company_id');
            $table->timestamps();
        });

        Schema::create('item_transfer_winery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_winery_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->integer('cantidad');
            $table->integer('lot_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transfer_winery');
        Schema::dropIfExists('transfer_wineries');
    }
};
