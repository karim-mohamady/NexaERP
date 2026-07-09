<?php

namespace App\Http\Controllers\Api;

use App\Models\AttendanceRecord;

class AttendanceRecordController extends GenericCrudController
{
    protected string $modelClass = AttendanceRecord::class;
    protected array $searchable = array (
  0 => 'status',
);
}