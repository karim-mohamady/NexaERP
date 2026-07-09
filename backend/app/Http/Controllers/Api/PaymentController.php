<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;

class PaymentController extends GenericCrudController
{
    protected string $modelClass = Payment::class;
    protected array $searchable = array (
  0 => 'reference',
  1 => 'method',
  2 => 'type',
);
}