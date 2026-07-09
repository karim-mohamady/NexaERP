<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;

class PurchaseOrderController extends GenericCrudController
{
    protected string $modelClass = PurchaseOrder::class;
    protected array $searchable = array (
  0 => 'number',
  1 => 'status',
);
}