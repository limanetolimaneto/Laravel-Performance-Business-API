<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class ReportService
{
    /**
     * Report 1
     * Sales Summary Report
     *
     * Optimized with Query Builder instead of Eloquent
     * for better performance in large datasets and PDF exports.
     */
    public function salesSummary(Request $request)
    {
        DB::listen(function ($query) {
            logger($query->sql);
            logger($query->bindings);
            logger($query->time);
        });

        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        // return Sale::with(['client', 'products'])
        //                 ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
        //                     $query->whereBetween('created_at', [
        //                         $startDate,
        //                         $endDate
        //                     ]);
        //                 })
        //                 ->orderByDesc('created_at')
        //                 ->get()
        //                 ->map(function ($sale) {
        //                     return [
        //                         'id' => $sale->id,
        //                         'client_name' => $sale->client->name,
        //                         'total_amount' => $sale->total_amount,
        //                         'created_at' => $sale->created_at,
        //                         'total_items' => $sale->products->count(),
        //                     ];
        //                 });

        return DB::table('sales')
            ->join('clients', 'sales.client_id', '=', 'clients.id')
            ->join('product_sale', 'sales.id', '=', 'product_sale.sale_id')
            ->select(
                'sales.id',
                'clients.name as client_name',
                'sales.total_amount',
                'sales.created_at',
                DB::raw('COUNT(product_sale.id) as total_items')
            )
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sales.created_at', [
                    $startDate,
                    $endDate
                ]);
            })
            ->groupBy(
                'sales.id',
                'clients.name',
                'sales.total_amount',
                'sales.created_at'
            )
            ->orderByDesc('sales.created_at')
            ->get();
    }

    /**
     * Report 2
     * Top Selling Products Report
     *
     * Aggregation queries are much faster using
     * Query Builder than Eloquent relationships.
     */
    public function topSellingProducts()
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_revenue')
            )
            ->groupBy(
                'products.id',
                'products.name'
            )
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();
    }
}