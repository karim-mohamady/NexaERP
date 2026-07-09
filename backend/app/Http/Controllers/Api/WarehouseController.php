<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;

class WarehouseController extends GenericCrudController
{
    protected string $modelClass = Warehouse::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'code',
  2 => 'location',
);
}