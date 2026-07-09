<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;

class DepartmentController extends GenericCrudController
{
    protected string $modelClass = Department::class;
    protected array $searchable = array (
  0 => 'name',
);
}