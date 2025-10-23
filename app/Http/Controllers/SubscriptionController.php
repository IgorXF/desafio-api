<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class SubscriptionController extends Controller
{
    
    private function getFixedUser()
    {
        return User::find(1); 
    }

    public function getUser()
    {
        $user = $this->getFixedUser();
        return response()->json($user);
    }

    public function getPlans()
    {
        $plans = Plan::all();
        return response()->json($plans);
    }

    public function getActiveSubscription()
    {
        $user = $this->getFixedUser();

        $activeSubscription = Subscription::where('user_id', $user->id)
                                          ->where('is_active', true)
                                          ->with('plan') 
                                          ->first();

        if (!$activeSubscription) {
            return response()->json(['message' => 'Nenhum plano contratado.'], 404);
        }

        return response()->json($activeSubscription);
    }

    public function subscribe(Request $request)
    {
        // validacao pq precisamos saber qual plano o usuario quer
        $request->validate(['plan_id' => 'required|exists:plans,id']);
        
        $user = $this->getFixedUser();
        $plan = Plan::find($request->plan_id);
        $today = Carbon::today();

        // verifica se já existe um contrato ativo
        $existingSubscription = Subscription::where('user_id', $user->id)
                                            ->where('is_active', true)
                                            ->first();
        
        if ($existingSubscription) {
            return response()->json(['message' => 'Usuário já possui um plano ativo. Use a rota /api/switch-plan para trocar.'], 400);
        }

        // usa uma "Transaction" para garantir que se algo falhar n cria a assinatura nem o pagamento
        $subscription = DB::transaction(function () use ($user, $plan, $today) {

            $newSubscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'original_subscription_date' => $today,
                'current_plan_starts_at' => $today,
                'next_billing_date' => $today->copy()->addMonth(),
                'is_active' => true,
            ]);

            Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $newSubscription->id,
                'description' => 'Contratação inicial - ' . $plan->name,
                'amount' => $plan->price,
                'credit_used' => 0,
                'total_charged' => $plan->price,
                'payment_method' => 'simulated_pix', // 
            ]);

            return $newSubscription;
        });

        return response()->json($subscription->load('plan'), 201); 
    }

    public function switchPlan(Request $request)
    {
        $request->validate(['new_plan_id' => 'required|exists:plans,id']);

        $user = $this->getFixedUser();
        $newPlan = Plan::find($request->new_plan_id);
        $today = Carbon::today();

        $subscription = Subscription::where('user_id', $user->id)
                                    ->where('is_active', true)
                                    ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Nenhum plano ativo encontrado. Use /api/subscribe para contratar primeiro.'], 404);
        }

        $oldPlan = Plan::find($subscription->plan_id);

        if ($oldPlan->id === $newPlan->id) {
             return response()->json(['message' => 'Você já está neste plano.'], 400);
        }

        
        $cycleStartDate = Carbon::parse($subscription->current_plan_starts_at);
        $cycleEndDate = Carbon::parse($subscription->next_billing_date);

        $totalDaysInCycle = $cycleStartDate->diffInDays($cycleEndDate);
        
        $daysUsed = $cycleStartDate->diffInDays($today);

        if ($totalDaysInCycle <= 0 || $daysUsed < 0) {
            $credit = 0;
        } else {
            $daysRemaining = $totalDaysInCycle - $daysUsed;

            $dailyRate = $oldPlan->price / $totalDaysInCycle;

            $credit = $dailyRate * $daysRemaining;
        }

        $totalCharged = $newPlan->price - $credit;

        if ($totalCharged < 0) {
            $totalCharged = 0;
        }

        
        $updatedSubscription = DB::transaction(function () use ($subscription, $user, $newPlan, $today, $credit, $totalCharged, $oldPlan) {
            
            $subscription->update([
                'plan_id' => $newPlan->id,
                'current_plan_starts_at' => $today, 
            ]);

            Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'description' => "Troca de Plano ({$oldPlan->name} -> {$newPlan->name})",
                'amount' => $newPlan->price,
                'credit_used' => $credit,
                'total_charged' => $totalCharged,
                'payment_method' => 'simulated_pix_credit',
            ]);

            return $subscription;
        });
        
        return response()->json($updatedSubscription->load('plan'));
    }
}