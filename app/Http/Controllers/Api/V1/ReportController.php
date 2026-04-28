<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function salesSummary(Request $request)
    {
        return response()->json(
            $this->reportService->salesSummary($request)
        );
    }

    public function topSellingProducts()
    {
        return response()->json(
            $this->reportService->topSellingProducts()
        );
    }
}
