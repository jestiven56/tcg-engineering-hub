<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Project;

class AuditEventController extends Controller
{
    public function index(Project $project)
    {
        $events = \App\Models\AuditEvent::with('actor')
            ->where(function ($query) use ($project) {
                // Eventos del proyecto
                $query->where('entity_type', 'project')
                      ->where('entity_id', $project->id);
            })
            ->orWhere(function ($query) use ($project) {
                // Eventos de artefactos del proyecto
                $query->where('entity_type', 'artifact')
                      ->whereIn('entity_id', $project->artifacts()->pluck('id'));
            })
            ->orWhere(function ($query) use ($project) {
                // Eventos de módulos del proyecto
                $query->where('entity_type', 'module')
                      ->whereIn('entity_id', $project->modules()->pluck('id'));
            })
            ->latest('created_at')
            ->paginate(20);

        return ApiResponse::success($events);
    }
}