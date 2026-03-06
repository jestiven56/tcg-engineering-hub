<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Discovery = 'discovery';
    case Execution = 'execution';
    case Delivered = 'delivered';
}