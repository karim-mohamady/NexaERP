<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRequest extends Model
{
    use HasFactory;

    protected $table = 'workflow_requests';
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

    public function steps()
    {
        return $this->hasMany(WorkflowRequestStep::class)->orderBy('approval_order');
    }
}
