<?php

namespace Database\Seeders;

use App\Enums\ArtifactStatus;
use App\Enums\ArtifactType;
use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@tcg.com')->first();

        $project = Project::create([
            'name'        => 'Portal de Pagos',
            'client_name' => 'Banco Nacional',
            'status'      => 'discovery',
            'created_by'  => $admin->id,
        ]);

        // Crear los 7 artefactos automáticamente para el proyecto
        foreach (ArtifactType::cases() as $type) {
            Artifact::create([
                'project_id'    => $project->id,
                'type'          => $type,
                'status'        => ArtifactStatus::NotStarted,
                'owner_user_id' => null,
                'content_json'  => null,
            ]);
        }
    }
}