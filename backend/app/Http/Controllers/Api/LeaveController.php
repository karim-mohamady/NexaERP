<?php

namespace App\Http\Controllers\Api;

use App\Models\LeaveRequest;

class LeaveController extends GenericCrudController
{
    protected string $modelClass = LeaveRequest::class;
    protected array $searchable = array (
  0 => 'type',
  1 => 'status',
  2 => 'reason',
);
}