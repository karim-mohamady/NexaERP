<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $companyId = $request->user()->company_id;

        return response()->json([
            'total_sales' => Invoice::where('company_id', $companyId)->sum('grand_total'),
            'paid_sales' => Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('grand_total'),
            'overdue_sales' => Invoice::where('company_id', $companyId)->where('status', 'overdue')->sum('grand_total'),
            'rows' => Invoice::where('company_id', $companyId)->latest()->limit(30)->get(),
        ]);
    }

    public function purchases(Request $request)
    {
        $companyId = $request->user()->company_id;

        return response()->json([
            'total_purchases' => PurchaseOrder::where('company_id', $companyId)->sum('grand_total'),
            'rows' => PurchaseOrder::where('company_id', $companyId)->latest()->limit(30)->get(),
        ]);
    }

    public function inventory(Request $request)
    {
        $companyId = $request->user()->company_id;
        $products = Product::where('company_id', $companyId)->get();

        return response()->json([
            'valuation' => $products->sum(fn ($product) => $product->stock_quantity * $product->cost_price),
            'low_stock_count' => $products->filter(fn ($product) => $product->stock_quantity <= $product->low_stock_threshold)->count(),
            'rows' => $products->values(),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $companyId = $request->user()->company_id;
        $income = Invoice::where('company_id', $companyId)->sum('grand_total');
        $expenses = Expense::where('company_id', $companyId)->sum('amount');

        return response()->json([
            'income' => round((float) $income, 2),
            'expenses' => round((float) $expenses, 2),
            'net_profit' => round((float) ($income - $expenses), 2),
        ]);
    }
}