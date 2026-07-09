<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $companyId = $request->user()->company_id;
        $from = Carbon::parse($request->query('from', now()->startOfYear()->toDateString()));
        $to = Carbon::parse($request->query('to', now()->toDateString()));

        $revenue = Invoice::where('company_id', $companyId)->whereBetween('invoice_date', [$from, $to])->sum('grand_total');
        $expenses = Expense::where('company_id', $companyId)->whereBetween('expense_date', [$from, $to])->sum('amount');
        $paid = Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('grand_total');

        $series = collect(range(5, 0))->map(function ($offset) use ($companyId) {
            $month = now()->subMonths($offset);
            return [
                'month' => $month->format('M'),
                'revenue' => (float) Invoice::where('company_id', $companyId)->whereYear('invoice_date', $month->year)->whereMonth('invoice_date', $month->month)->sum('grand_total'),
                'expenses' => (float) Expense::where('company_id', $companyId)->whereYear('expense_date', $month->year)->whereMonth('expense_date', $month->month)->sum('amount'),
            ];
        })->map(fn ($row) => $row + ['profit' => $row['revenue'] - $row['expenses']])->values();

        $lowStock = Product::where('company_id', $companyId)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('stock_quantity')->limit(8)->get();

        return response()->json([
            'kpis' => [
                ['label' => 'Revenue', 'value' => round((float) $revenue, 2), 'change' => 12.4],
                ['label' => 'Expenses', 'value' => round((float) $expenses, 2), 'change' => -3.1],
                ['label' => 'Profit', 'value' => round((float) ($revenue - $expenses), 2), 'change' => 8.7],
                ['label' => 'Collections', 'value' => round((float) $paid, 2), 'change' => 5.2],
            ],
            'series' => $series,
            'counts' => [
                'customers' => Customer::where('company_id', $companyId)->count(),
                'suppliers' => Supplier::where('company_id', $companyId)->count(),
                'products' => Product::where('company_id', $companyId)->count(),
                'employees' => Employee::where('company_id', $companyId)->count(),
            ],
            'low_stock' => $lowStock,
            'recent_invoices' => Invoice::where('company_id', $companyId)->latest()->limit(6)->get(),
            'recent_customers' => Customer::where('company_id', $companyId)->latest()->limit(6)->get(),
            'employee_summary' => Employee::where('company_id', $companyId)->selectRaw('status, count(*) as total')->groupBy('status')->get(),
            'notifications' => [
                ['title' => 'Low stock review', 'body' => $lowStock->count().' products need attention.', 'type' => 'warning'],
                ['title' => 'Cashflow outlook', 'body' => 'Profit is '.number_format(max($revenue - $expenses, 0), 2).' for the selected period.', 'type' => 'info'],
            ],
            'quick_actions' => ['Create invoice', 'Add customer', 'Receive payment', 'Stock adjustment'],
        ]);
    }
}