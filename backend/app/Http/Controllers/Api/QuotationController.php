<?php

namespace App\Http\Controllers\Api;

use App\Models\Quotation;

class QuotationController extends GenericCrudController
{
    protected string $modelClass = Quotation::class;
    protected array $searchable = array (
  0 => 'number',
  1 => 'status',
);
}