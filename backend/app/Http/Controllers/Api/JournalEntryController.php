<?php

namespace App\Http\Controllers\Api;

use App\Models\JournalEntry;

class JournalEntryController extends GenericCrudController
{
    protected string $modelClass = JournalEntry::class;
    protected array $searchable = array (
  0 => 'number',
  1 => 'status',
  2 => 'memo',
);
}