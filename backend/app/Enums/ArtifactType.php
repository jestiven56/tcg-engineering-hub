<?php

namespace App\Enums;

enum ArtifactType: string
{
    case StrategicAlignment = 'strategic_alignment';
    case BigPicture = 'big_picture';
    case DomainBreakdown = 'domain_breakdown';
    case ModuleMatrix = 'module_matrix';
    case ModuleEngineering = 'module_engineering';
    case SystemArchitecture = 'system_architecture';
    case PhaseScope = 'phase_scope';
}