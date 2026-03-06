<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ModuleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Module\StoreModuleRequest;
use App\Http\Requests\Module\UpdateModuleRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Module;
use App\Models\Project;
use App\Services\AuditService;
use App\Services\ModuleValidationService;

class ModuleController extends Controller
{
    public function __construct(
        private ModuleValidationService $validationService,
        private AuditService $auditService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('viewAny', Module::class);

        $modules = $project->modules()
            ->latest()
            ->paginate(10);

        return ApiResponse::success($modules);
    }

    public function store(StoreModuleRequest $request, Project $project)
    {
        $this->authorize('create', Module::class);

        $module = $project->modules()->create($request->validated());

        $this->auditService->log('module', $module->id, 'created', null, $module->toArray());

        return ApiResponse::success($module, 'Módulo creado.', 201);
    }

    public function show(Project $project, Module $module)
    {
        $this->authorize('view', $module);

        $check = $this->validationService->canValidate($module);

        $module->can_validate  = $check['allowed'];
        $module->block_reasons = $check['reasons'];

        return ApiResponse::success($module);
    }

    public function update(UpdateModuleRequest $request, Project $project, Module $module)
    {
        $this->authorize('update', $module);

        $before = $module->toArray();
        $module->update($request->validated());

        $this->auditService->log(
            'module',
            $module->id,
            'updated',
            $before,
            $module->fresh()->toArray()
        );

        return ApiResponse::success($module, 'Módulo actualizado.');
    }

    public function destroy(Project $project, Module $module)
    {
        $this->authorize('delete', $module);

        $before = $module->toArray();
        $module->delete();

        $this->auditService->log('module', $module->id, 'updated', $before, ['deleted_at' => now()]);

        return ApiResponse::success(null, 'Módulo eliminado.');
    }

    public function validates(Project $project, Module $module)
    {
        $this->authorize('validate', $module);

        $check = $this->validationService->canValidate($module);

        if (!$check['allowed']) {
            return ApiResponse::error(
                'El módulo no puede ser validado.',
                $check['reasons'],
                422
            );
        }

        $before = $module->toArray();

        $module->update(['status' => ModuleStatus::Validated]);

        $this->auditService->log(
            'module',
            $module->id,
            'validated',
            ['status' => $before['status']],
            ['status' => ModuleStatus::Validated->value]
        );

        return ApiResponse::success($module, 'Módulo validado exitosamente.');
    }
}