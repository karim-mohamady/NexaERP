<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;

class EmployeeController extends GenericCrudController
{
    protected string $modelClass = Employee::class;
    protected array $searchable = array (
  0 => 'employee_code',
  1 => 'name',
  2 => 'email',
  3 => 'phone',
  4 => 'status',
);
}