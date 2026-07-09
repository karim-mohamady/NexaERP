<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;

class CustomerController extends GenericCrudController
{
    protected string $modelClass = Customer::class;
    protected array $searchable = array (
  0 => 'name',
  1 => 'email',
  2 => 'phone',
  3 => 'group',
  4 => 'status',
  5 => 'source',
);
}