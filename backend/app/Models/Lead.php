<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'leads';
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'value' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'is_active' => 'boolean',
        'is_cash_bank' => 'boolean',
    ];
}