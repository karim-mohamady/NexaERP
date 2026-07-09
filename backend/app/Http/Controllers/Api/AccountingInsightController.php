<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AccountingInsightController extends Controller
{
    public function trialBalance(Request $request)
    {
        return response()->json(Account::where('company_id', $request->user()->company_id)->get()->map(fn ($account) => [
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'debit' => in_array($account->type, ['asset', 'expense'], true) ? (float) $account->opening_balance : 0,
            'credit' => in_array($account->type, ['liability', 'equity', 'income'], true) ? (float) $account->opening_balance : 0,
        ]));
    }

    public function profitLoss(Request $request)
    {
        $income = (float) Invoice::where('company_id', $request->user()->company_id)->sum('grand_total');
        $expenses = (float) Expense::where('company_id', $request->user()->company_id)->sum('amount');

        return response()->json(['income' => $income, 'expenses' => $expenses, 'net_profit' => $income - $expenses]);
    }

    public function cashBank(Request $request)
    {
        return response()->json(Account::where('company_id', $request->user()->company_id)->where('is_cash_bank', true)->get());
    }
}