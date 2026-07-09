<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;

class ProductController extends GenericCrudController
{
    protected string $modelClass = Product::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'sku',
  2 => 'barcode',
);
}