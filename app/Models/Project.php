<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'client_name', 'status', 'created_by'];

    protected $casts = [
        'status' => ProjectStatus::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function artifacts()
    {
        return $this->hasMany(Artifact::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function auditEvents()
    {
        return $this->hasMany(AuditEvent::class, 'entity_id')
                    ->where('entity_type', 'project')
                    ->latest();
    }
}