<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;

class ExpenseController extends GenericCrudController
{
    protected string $modelClass = Expense::class;
    protected array $searchable = array (
  0 => 'category',
  1 => 'description',
  2 => 'payment_method',
);
}