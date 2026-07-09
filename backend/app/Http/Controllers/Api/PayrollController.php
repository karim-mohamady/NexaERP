<?php

namespace App\Http\Controllers\Api;

use App\Models\Payroll;

class PayrollController extends GenericCrudController
{
    protected string $modelClass = Payroll::class;
    protected array $searchable = array (
  0 => 'period',
  1 => 'status',
);
}