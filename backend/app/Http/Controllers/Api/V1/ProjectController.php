<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ArtifactStatus;
use App\Enums\ArtifactType;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UpdateProjectStatusRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Artifact;
use App\Models\Project;
use App\Services\ArtifactGateService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ArtifactGateService $gateService,
        private AuditService $auditService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with(['creator', 'artifacts'])
            ->latest()
            ->paginate(10);

        return ApiResponse::success($projects);
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        $project = Project::create([
            'name'        => $request->name,
            'client_name' => $request->client_name,
            'status'      => ProjectStatus::Draft,
            'created_by'  => $request->user()->id,
        ]);

        // Crear los 7 artefactos automáticamente
        foreach (ArtifactType::cases() as $type) {
            Artifact::create([
                'project_id'   => $project->id,
                'type'         => $type,
                'status'       => ArtifactStatus::NotStarted,
                'content_json' => null,
            ]);
        }

        $this->auditService->log('project', $project->id, 'created', null, $project->toArray());

        return ApiResponse::success($project->load('artifacts'), 'Proyecto creado.', 201);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['creator', 'artifacts.owner', 'modules']);

        // Agregar blocking reason a cada artefacto
        $project->artifacts->transform(function ($artifact) use ($project) {
            $check = $this->gateService->canMarkDone($project, $artifact->type);
            $artifact->blocked_reason = $check['allowed'] ? null : $check['reason'];
            return $artifact;
        });

        return ApiResponse::success($project);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $before = $project->toArray();
        $project->update($request->validated());

        $this->auditService->log('project', $project->id, 'updated', $before, $project->fresh()->toArray());

        return ApiResponse::success($project, 'Proyecto actualizado.');
    }

    public function updateStatus(UpdateProjectStatusRequest $request, Project $project)
    {
        $this->authorize('updateStatus', $project);

        $newStatus = $request->status;
        $before    = $project->toArray();

        // Gate 4: discovery → execution
        if (
            $project->status->value === ProjectStatus::Discovery->value &&
            $newStatus === ProjectStatus::Execution->value
        ) {
            $check = $this->gateService->canMoveToExecution($project);

            if (!$check['allowed']) {
                return ApiResponse::error(
                    'No se puede mover el proyecto a Execution.',
                    $check['reasons'],
                    422
                );
            }
        }

        $project->update(['status' => $newStatus]);

        $this->auditService->log(
            'project',
            $project->id,
            'status_changed',
            ['status' => $before['status']],
            ['status' => $newStatus]
        );

        return ApiResponse::success($project, 'Estado del proyecto actualizado.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $before = $project->toArray();
        $project->delete();

        $this->auditService->log('project', $project->id, 'updated', $before, ['deleted_at' => now()]);

        return ApiResponse::success(null, 'Proyecto archivado.');
    }
}