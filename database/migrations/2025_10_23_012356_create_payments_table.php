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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // o user q pagou
            $table->foreignId('subscription_id')->constrained('subscriptions'); // o subs 
            $table->string('description'); // descrever a operacao feita

            //troca
            $table->decimal('amount', 8, 2);
            $table->decimal('credit_used', 8, 2)->default(0); 
            $table->decimal('total_charged', 8, 2); 

            $table->string('payment_method')->default('simulated_pix'); //pagamento fakezin
            $table->timestamps(); // created_at vai ser a data do pagamento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};