<?php

namespace App\Http\Controllers\Api;

use App\Models\JobTitle;

class JobTitleController extends GenericCrudController
{
    protected string $modelClass = JobTitle::class;
    protected array $searchable = array (
  0 => 'title',
);
}