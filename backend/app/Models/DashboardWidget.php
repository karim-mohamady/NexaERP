<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $table = 'dashboard_widgets';
    protected $guarded = [];

    protected $casts = [
        'config' => 'array',
        'data' => 'array',
        'features' => 'array',
        'filters' => 'array',
        'selected_columns' => 'array',
        'is_active' => 'boolean',
        'is_final' => 'boolean',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'acted_at' => 'datetime',
    ];
}