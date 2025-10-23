<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        User::query()->delete();


        User::create([
            'name' => 'Usuário Fixo',
            'email' => 'usuario@fixo.com',
            'password' => Hash::make('password123')
        ]);

        $this->call(PlanSeeder::class);
    }
}