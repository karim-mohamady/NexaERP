<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;

class AccountController extends GenericCrudController
{
    protected string $modelClass = Account::class;
    protected array $searchable = array (
  0 => 'code',
  1 => 'name',
  2 => 'type',
);
}