<?php

namespace App\Http\Controllers\Api;

use App\Models\Lead;

class LeadController extends GenericCrudController
{
    protected string $modelClass = Lead::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'email',
  2 => 'phone',
  3 => 'stage',
  4 => 'source',
);
}