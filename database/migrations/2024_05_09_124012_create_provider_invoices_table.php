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
        Schema::create('provider_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained();
            $table->string('codigo');
            $table->date('fecha');
            $table->string('formaPago');
            $table->decimal('subTotal', 10, 2);
            $table->decimal('totalImpuestos', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('descuento', 10, 2);
            $table->decimal('valorDescuento', 10, 2);
            $table->foreignId('cash_id')->nullable()->constrained();
            $table->text('obeservaciones')->nullable();
            $table->integer('company_id');
            $table->timestamps();
        });

        Schema::create('item_provider_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->integer('cantidad')->unsigned();
            $table->decimal('precio_unitario', 20, 2);
            $table->decimal('descuento', 5, 2);
            $table->decimal('valor_descuento', 20, 2);
            $table->decimal('subtotal', 20, 2);
            $table->decimal('impuestos', 20, 2);
            $table->decimal('precio_total', 20, 2);
            $table->integer('lot_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_invoices');
    }
};
