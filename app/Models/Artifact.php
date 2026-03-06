<?php

namespace App\Models;

use App\Enums\ArtifactStatus;
use App\Enums\ArtifactType;
use Illuminate\Database\Eloquent\Model;

class Artifact extends Model
{
    protected $fillable = [
        'project_id', 'type', 'status',
        'owner_user_id', 'content_json', 'completed_at'
    ];

    protected $casts = [
        'type'         => ArtifactType::class,
        'status'       => ArtifactStatus::class,
        'content_json' => 'array',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}