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

        // SEE README → D.S_2 

            /*
            |--------------------------------------------------------------------------
            | Aggregate Query Optimization
            |--------------------------------------------------------------------------
            |
            | Uncomment below to test using Laravel Debugbar.
            |
            */
            // Client::all()->each(function ($client) {
            //     $client->update([
            //         'total_spent' => $client->sales()->sum('total_amount')
            //     ]);
            // });
            /*
            |--------------------------------------------------------------------------
            | Optimized Solution using DB::raw
            |--------------------------------------------------------------------------
            |
            | Result:
            | only 1 database query
            |
            */
            Client::query()->update([
                'total_spent' => DB::raw("(
                    SELECT COALESCE(SUM(total_amount), 0)
                    FROM sales
                    WHERE sales.client_id = clients.id
                )")
            ]);
        // ==================

    }
}
