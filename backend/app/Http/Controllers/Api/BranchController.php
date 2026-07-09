<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;

class BranchController extends GenericCrudController
{
    protected string $modelClass = Branch::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'code',
  2 => 'city',
);
}