<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;

class CompanyController extends GenericCrudController
{
    protected string $modelClass = Company::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'email',
  2 => 'phone',
);
}