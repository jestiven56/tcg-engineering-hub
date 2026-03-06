<?php

namespace Tests\Traits;

use App\Enums\ArtifactStatus;
use App\Enums\ArtifactType;
use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesTestData
{
    protected function createUser(string $role): User
    {
        return User::create([
            'name'     => "Test {$role}",
            'email'    => "{$role}_" . uniqid() . "@tcg.com",
            'password' => Hash::make('password'),
            'role'     => $role,
        ]);
    }

    protected function createProject(User $user, string $status = 'draft'): Project
    {
        $project = Project::create([
            'name'        => 'Test Project',
            'client_name' => 'Test Client',
            'status'      => $status,
            'created_by'  => $user->id,
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

        return $project;
    }

    protected function setArtifactStatus(Project $project, string $type, string $status): void
    {
        $project->artifacts()
            ->where('type', $type)
            ->update([
                'status'       => $status,
                'completed_at' => $status === 'done' ? now() : null,
            ]);
    }

    protected function createValidatedModule(Project $project): Module
    {
        return Module::create([
            'project_id'     => $project->id,
            'domain'         => 'Test Domain',
            'name'           => 'Test Module ' . uniqid(),
            'status'         => 'validated',
            'objective'      => 'Test objective',
            'inputs'         => ['input1'],
            'outputs'        => ['output1'],
            'responsibility' => 'Test responsibility',
        ]);
    }
}