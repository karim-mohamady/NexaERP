<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductCategory;

class ProductCategoryController extends GenericCrudController
{
    protected string $modelClass = ProductCategory::class;
    protected array $searchable = array (
  0 => 'name',
);
}