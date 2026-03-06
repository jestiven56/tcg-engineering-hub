<?php

namespace App\Services;

use App\Models\Module;

class ModuleValidationService
{
    /**
     * Verifica si un módulo puede marcarse como "validated".
     * Retorna array con: ['allowed' => bool, 'reasons' => array]
     */
    public function canValidate(Module $module): array
    {
        $reasons = [];

        if (empty($module->objective)) {
            $reasons[] = "El campo 'objective' es obligatorio.";
        }

        if (empty($module->inputs) || count($module->inputs) < 1) {
            $reasons[] = "El módulo debe tener al menos 1 input.";
        }

        if (empty($module->outputs) || count($module->outputs) < 1) {
            $reasons[] = "El módulo debe tener al menos 1 output.";
        }

        if (empty($module->responsibility)) {
            $reasons[] = "El campo 'responsibility' es obligatorio.";
        }

        return [
            'allowed' => empty($reasons),
            'reasons' => $reasons,
        ];
    }
}