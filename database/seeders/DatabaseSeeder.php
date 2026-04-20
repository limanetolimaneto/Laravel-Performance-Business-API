<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Client;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test',
            'email' => 'test@email.com',
            'password' => Hash::make('123'),
        ]);

        Client::factory(10)->create();
        
        Sale::factory(1000)->create();

        Client::query()->update([
            'total_spent' => DB::raw("(
                SELECT COALESCE(SUM(total_amount), 0)
                FROM sales
                WHERE sales.client_id = clients.id
            )")
        ]);
        // Vantagens:
        // 1 única query
        // extremamente rápido
        // escala bem com 1000+ records
        // Desvantagem:
        // menos legível
        // depende de SQL direto

    }
}
