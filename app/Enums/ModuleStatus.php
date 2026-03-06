<?php

namespace App\Enums;

enum ModuleStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';
    case ReadyForBuild = 'ready_for_build';
}