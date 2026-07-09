<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;

class SalesOrderController extends GenericCrudController
{
    protected string $modelClass = SalesOrder::class;
    protected array $searchable = array (
  0 => 'number',
  1 => 'status',
);
}