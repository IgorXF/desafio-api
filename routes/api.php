<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController; 

// retornam dados
Route::get('/user', [SubscriptionController::class, 'getUser']);
Route::get('/plans', [SubscriptionController::class, 'getPlans']);
Route::get('/subscription', [SubscriptionController::class, 'getActiveSubscription']);

// permissoes de alterar dados
Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
Route::post('/switch-plan', [SubscriptionController::class, 'switchPlan']);