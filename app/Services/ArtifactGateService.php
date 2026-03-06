<?php

namespace App\Services;

use App\Enums\ArtifactStatus;
use App\Enums\ArtifactType;
use App\Enums\ModuleStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;

class ArtifactGateService
{
    // N mínimo de módulos validados para completar system_architecture
    private int $minValidatedModules;

    public function __construct()
    {
        $this->minValidatedModules = config('tcg.min_validated_modules', 3);
    }

    /**
     * Verifica si un artefacto puede marcarse como "done".
     * Retorna array con: ['allowed' => bool, 'reason' => string|null]
     */
    public function canMarkDone(Project $project, string $artifactType): array
    {
        return match ($artifactType) {
            ArtifactType::DomainBreakdown->value  => $this->checkDomainBreakdown($project),
            ArtifactType::ModuleMatrix->value      => $this->checkModuleMatrix($project),
            ArtifactType::SystemArchitecture->value => $this->checkSystemArchitecture($project),
            default                                => ['allowed' => true, 'reason' => null],
        };
    }

    /**
     * Verifica si el proyecto puede pasar de discovery → execution.
     * Retorna array con: ['allowed' => bool, 'reasons' => array]
     */
    public function canMoveToExecution(Project $project): array
    {
        $requiredTypes = [
            ArtifactType::StrategicAlignment->value,
            ArtifactType::BigPicture->value,
            ArtifactType::DomainBreakdown->value,
            ArtifactType::ModuleMatrix->value,
        ];

        $artifacts = $project->artifacts()
            ->whereIn('type', $requiredTypes)
            ->get()
            ->keyBy(fn($artifact) => $artifact->type->value);

        $reasons = [];

        foreach ($requiredTypes as $type) {
            $artifact = $artifacts->get($type);
            if (!$artifact || $artifact->status->value !== ArtifactStatus::Done->value) {
                $label = $this->getTypeLabel($type);
                $reasons[] = "El artefacto '{$label}' debe estar completado (done).";
            }
        }

        return [
            'allowed' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    // --- Gates individuales ---

    private function checkDomainBreakdown(Project $project): array
    {
        $bigPicture = $project->artifacts()
            ->where('type', ArtifactType::BigPicture->value)
            ->first();

        if (!$bigPicture || $bigPicture->status->value !== ArtifactStatus::Done->value) {
            return [
                'allowed' => false,
                'reason'  => "Big Picture debe estar completado antes de finalizar Domain Breakdown.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    private function checkModuleMatrix(Project $project): array
    {
        $domainBreakdown = $project->artifacts()
            ->where('type', ArtifactType::DomainBreakdown->value)
            ->first();

        if (!$domainBreakdown || $domainBreakdown->status->value !== ArtifactStatus::Done->value) {
            return [
                'allowed' => false,
                'reason'  => "Domain Breakdown debe estar completado antes de finalizar Module Matrix.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    private function checkSystemArchitecture(Project $project): array
    {
        $validatedCount = $project->modules()
            ->where('status', ModuleStatus::Validated->value)
            ->count();

        if ($validatedCount < $this->minValidatedModules) {
            return [
                'allowed' => false,
                'reason'  => "Se necesitan al menos {$this->minValidatedModules} módulos validados. Actualmente hay {$validatedCount}.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'strategic_alignment' => 'Strategic Alignment',
            'big_picture'         => 'Big Picture',
            'domain_breakdown'    => 'Domain Breakdown',
            'module_matrix'       => 'Module Matrix',
            'module_engineering'  => 'Module Engineering',
            'system_architecture' => 'System Architecture',
            'phase_scope'         => 'Phase Scope',
            default               => $type,
        };
    }
}