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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['CC', 'CE', 'NIT', 'TI', 'PB']);
            $table->string('numero_documento', 20);
            $table->string('nombre_razonsocial', 30);
            $table->string('telefono', 10);
            $table->string('correo', 30)->nullable();
            $table->string('direccion', 30)->nullable();
            $table->string('municipio', 30)->nullable();
            $table->boolean('responsable_iva')->default(1);
            $table->integer('company_id');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['CC', 'CE', 'NIT', 'TI', 'PB']);
            $table->string('numero_documento', 20);
            $table->string('nombre_razonsocial', 30);
            $table->string('telefono', 10);
            $table->string('correo', 30)->nullable();
            $table->string('direccion', 30)->nullable();
            $table->string('municipio', 30)->nullable();
            $table->integer('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
        Schema::dropIfExists('customers');
    }
};
