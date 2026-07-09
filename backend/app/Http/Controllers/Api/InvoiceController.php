<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;

class InvoiceController extends GenericCrudController
{
    protected string $modelClass = Invoice::class;
    protected array $searchable = array (
  0 => 'number',
  1 => 'status',
  2 => 'notes',
);
}