<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint', 500)->unique(); // URL del servicio push
            $table->string('public_key')->nullable(); // Clave p256dh
            $table->string('auth_token')->nullable(); // Token auth
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
