<?php

namespace App\Http\Controllers\Api;

use App\Models\Unit;

class UnitController extends GenericCrudController
{
    protected string $modelClass = Unit::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'symbol',
);
}