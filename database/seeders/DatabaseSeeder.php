<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Product;
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

        Supplier::factory(6)->create();

        $products = [ 'mouse' => '50.00' ,'keyboard' => '200.00','laptop' => '8000.00','router' => '450.00','charger' => '300.00',];
        foreach ($products as $key => $value) {
            Product::factory()->create([
                'supplier_id' => Supplier::pluck('id')->random(),
                'name' => $key,
                'price' => $value,
            ]);
            
        }

        $sales = Sale::factory(10)->create();
        
        foreach ($sales as $sale) {
            $total_amount = 0;
            $q1 = fake()->numberBetween(1, 4);
            $products = Product::inRandomOrder()->take($q1)->get();
            foreach ($products as $product) {
                $q2 = fake()->numberBetween(1, 4);
                $total_amount += $product->price * $q2;
                DB::table('product_sale')->insert([
                    'product_id' => $product->id,
                    'sale_id' => $sale->id,
                    'quantity' => $q2,
                    'amount' => $product->price * $q2,
                ]);
            }
            
            $sale->update([
                    'total_amount' => $total_amount
            ]);
        }

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
