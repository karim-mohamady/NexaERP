<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;

class AiCopilotService
{
    public function answer(int $companyId, string $question = ''): array
    {
        $revenue = (float) Invoice::where('company_id', $companyId)->sum('grand_total');
        $expenses = (float) Expense::where('company_id', $companyId)->sum('amount');
        $lowStock = Product::where('company_id', $companyId)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get();
        $topCustomers = Customer::where('company_id', $companyId)->orderByDesc('balance')->limit(3)->pluck('name')->values();

        return [
            'provider' => env('OPENAI_API_KEY') ? env('AI_PROVIDER', 'openai') : 'local-rules',
            'answer' => $this->ruleBasedAnswer($question, $revenue, $expenses, $lowStock->count(), $topCustomers->all()),
            'signals' => [
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
                'profit' => round($revenue - $expenses, 2),
                'low_stock_count' => $lowStock->count(),
                'top_customers' => $topCustomers,
            ],
            'recommendations' => [
                'Prioritize overdue invoice collection before approving discretionary expenses.',
                'Create replenishment purchase orders for low-stock products.',
                'Review high-value customers and schedule follow-up tasks this week.',
            ],
        ];
    }

    private function ruleBasedAnswer(string $question, float $revenue, float $expenses, int $lowStockCount, array $topCustomers): string
    {
        $question = strtolower($question);
        if (str_contains($question, 'stock') || str_contains($question, 'inventory')) {
            return $lowStockCount > 0
                ? "{$lowStockCount} products are at stockout risk. Reorder them before approving new sales commitments."
                : 'Inventory risk is currently low based on configured thresholds.';
        }

        if (str_contains($question, 'expense') || str_contains($question, 'unusual')) {
            return $expenses > ($revenue * 0.5)
                ? 'Expenses are elevated versus revenue. Audit payroll, rent, software, and logistics before month end.'
                : 'No major expense anomaly is visible in the current seeded data.';
        }

        if (str_contains($question, 'customer')) {
            return 'The most valuable customer segments should be built around: '.implode(', ', $topCustomers ?: ['active enterprise customers']).'.';
        }

        return 'Focus this week on approvals, collections, low-stock replenishment, and follow-up tasks for high-value customers.';
    }
}