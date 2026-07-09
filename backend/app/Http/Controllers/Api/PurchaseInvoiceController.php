<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseInvoice;

class PurchaseInvoiceController extends GenericCrudController
{
    protected string $modelClass = PurchaseInvoice::class;
    protected array $searchable = array (
  0 => 'number',
  1 => 'status',
);
}