<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ArtifactStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Artifact\UpdateArtifactRequest;
use App\Http\Requests\Artifact\UpdateArtifactStatusRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Artifact;
use App\Models\Project;
use App\Services\ArtifactGateService;
use App\Services\AuditService;

class ArtifactController extends Controller
{
    public function __construct(
        private ArtifactGateService $gateService,
        private AuditService $auditService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('viewAny', Artifact::class);

        $artifacts = $project->artifacts()
            ->with('owner')
            ->get()
            ->map(function ($artifact) use ($project) {
                $check = $this->gateService->canMarkDone($project, $artifact->type->value);
                $artifact->blocked_reason = $check['allowed'] ? null : $check['reason'];
                return $artifact;
            });

        return ApiResponse::success($artifacts);
    }

    public function show(Project $project, Artifact $artifact)
    {
        $this->authorize('view', $artifact);

        $check = $this->gateService->canMarkDone($project, $artifact->type->value);
        $artifact->blocked_reason = $check['allowed'] ? null : $check['reason'];

        return ApiResponse::success($artifact->load('owner'));
    }

    public function update(UpdateArtifactRequest $request, Project $project, Artifact $artifact)
    {
        $this->authorize('update', $artifact);

        $before = $artifact->toArray();
        $artifact->update($request->validated());

        $this->auditService->log(
            'artifact',
            $artifact->id,
            'updated',
            $before,
            $artifact->fresh()->toArray()
        );

        return ApiResponse::success($artifact, 'Artefacto actualizado.');
    }

    public function updateStatus(UpdateArtifactStatusRequest $request, Project $project, Artifact $artifact)
    {
        $this->authorize('updateStatus', $artifact);

        $newStatus = $request->status;
        $before    = $artifact->toArray();

        // Aplicar gates si se quiere marcar como done
        if ($newStatus === ArtifactStatus::Done->value) {
            $check = $this->gateService->canMarkDone($project, $artifact->type->value);

            if (!$check['allowed']) {
                return ApiResponse::error(
                    'No se puede completar este artefacto.',
                    [$check['reason']],
                    422
                );
            }
        }

        $completedAt = $newStatus === ArtifactStatus::Done->value ? now() : null;

        $artifact->update([
            'status'       => $newStatus,
            'completed_at' => $completedAt,
        ]);

        $this->auditService->log(
            'artifact',
            $artifact->id,
            'status_changed',
            ['status' => $before['status']],
            ['status' => $newStatus]
        );

        return ApiResponse::success($artifact, 'Estado del artefacto actualizado.');
    }
}