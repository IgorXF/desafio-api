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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // linka com a tabela de user que o laravel criou
            $table->foreignId('plan_id')->constrained('plans'); // linka a tabela de planos

            //a data de pagamento tem q ser sempre o mesmo dia do mês da subs
            $table->date('original_subscription_date'); // a daata da 1 contratação fixa o dia do mes

            // data que o plano de agora começou (caso precisa calcular troca)
            $table->date('current_plan_starts_at');

            // data da prox cobranca
            $table->date('next_billing_date');

            // o usuario so vai poder ter um subs ativo
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};