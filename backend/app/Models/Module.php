<?php

namespace App\Models;

use App\Enums\ModuleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'domain', 'name', 'status',
        'objective', 'inputs', 'data_structure', 'logic_rules',
        'outputs', 'responsibility', 'failure_scenarios',
        'audit_trail_requirements', 'dependencies', 'version_note'
    ];

    protected $casts = [
        'status'       => ModuleStatus::class,
        'inputs'       => 'array',
        'outputs'      => 'array',
        'dependencies' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}