<?php

namespace App\Enums;

enum ArtifactStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case Done = 'done';
}