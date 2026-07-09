<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;

class SupplierController extends GenericCrudController
{
    protected string $modelClass = Supplier::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'email',
  2 => 'phone',
  3 => 'status',
);
}