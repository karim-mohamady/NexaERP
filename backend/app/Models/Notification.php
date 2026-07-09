<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'settings' => 'array',
        'value' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'is_active' => 'boolean',
        'is_cash_bank' => 'boolean',
        'read_at' => 'datetime',
    ];
}
