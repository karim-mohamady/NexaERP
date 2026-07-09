<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;

class AiInsightController extends Controller
{
    public function insights(Request $request)
    {
        $companyId = $request->user()->company_id;
        $revenue = (float) Invoice::where('company_id', $companyId)->sum('grand_total');
        $expenses = (float) Expense::where('company_id', $companyId)->sum('amount');
        $lowStock = Product::where('company_id', $companyId)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $customers = Customer::where('company_id', $companyId)->count();

        return response()->json([
            'provider' => env('OPENAI_API_KEY') ? env('AI_PROVIDER', 'openai') : 'mock',
            'summary' => 'Revenue is '.number_format($revenue, 2).' against expenses of '.number_format($expenses, 2).'. The current demo data suggests a '.($revenue >= $expenses ? 'profitable' : 'loss-making').' operating window.',
            'sales_trend' => $revenue > 50000 ? 'Sales trend is healthy. Prioritize collections and renewals.' : 'Sales volume is early-stage. Add campaigns for high-value leads.',
            'inventory_risk' => $lowStock > 0 ? "{$lowStock} products are below threshold. Reorder before invoice fulfillment is affected." : 'Inventory risk is low across tracked products.',
            'customer_segments' => $customers > 10 ? 'Segment customers into strategic, repeat, and nurture groups based on invoice value.' : 'Keep a high-touch onboarding segment until customer volume grows.',
            'expense_anomalies' => $expenses > ($revenue * 0.65) ? 'Expenses are elevated versus revenue. Review payroll, rent, and marketing categories.' : 'No major expense anomaly detected in current data.',
            'actions' => [
                'Review low-stock SKUs and create purchase orders.',
                'Contact customers with unpaid invoices older than 14 days.',
                'Compare branch-level margins before next hiring decision.',
            ],
        ]);
    }

    public function analyze(Request $request)
    {
        $request->validate(['prompt' => ['nullable', 'string', 'max:2000']]);

        return response()->json([
            'answer' => 'AI assistant is ready. Add OPENAI_API_KEY to enable live provider calls; meanwhile NexaERP returns grounded mock analysis from your ERP data.',
        ]);
    }
}