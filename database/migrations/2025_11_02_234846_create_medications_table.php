<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            
            // Llave foránea para el usuario
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            
            // Campo para la foto
            $table->string('photo_path')->nullable();
            
            // Gestión de Stock
            $table->integer('total_stock')->default(0); // Stock total comprado
            $table->integer('current_stock')->default(0); // Stock restante
            $table->integer('dose_quantity')->default(1); // Cuántas unidades por toma (ej: 2 pastillas)
            
            // Configuración del Horario
            $table->integer('frequency_hours'); // Frecuencia (4, 8, 12, 24 horas)
            $table->time('start_time'); // Hora de la primera toma del día (ej: 08:00)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};