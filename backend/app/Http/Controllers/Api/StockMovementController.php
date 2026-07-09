<?php

namespace App\Http\Controllers\Api;

use App\Models\StockMovement;

class StockMovementController extends GenericCrudController
{
    protected string $modelClass = StockMovement::class;
    protected array $searchable = array (
  0 => 'reference',
  1 => 'type',
  2 => 'notes',
);
}