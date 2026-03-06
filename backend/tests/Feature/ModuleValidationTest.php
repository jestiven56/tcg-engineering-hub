<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesTestData;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModuleValidationTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_cannot_validate_module_with_missing_fields(): void
    {
        $engineer = $this->createUser('engineer');
        $project  = $this->createProject($engineer);

        // Módulo sin campos requeridos
        $module = Module::create([
            'project_id' => $project->id,
            'domain'     => 'Payments',
            'name'       => 'Incomplete Module',
            'status'     => 'draft',
            // objective, inputs, outputs, responsibility vacíos
        ]);

        $response = $this->actingAs($engineer)
            ->patchJson("/api/v1/projects/{$project->id}/modules/{$module->id}/validate");

        $response->assertStatus(422)
                 ->assertJsonPath('success', false)
                 ->assertJsonFragment(["El campo 'objective' es obligatorio."])
                 ->assertJsonFragment(["El módulo debe tener al menos 1 input."])
                 ->assertJsonFragment(["El módulo debe tener al menos 1 output."])
                 ->assertJsonFragment(["El campo 'responsibility' es obligatorio."]);
    }

    public function test_can_validate_module_with_all_required_fields(): void
    {
        $engineer = $this->createUser('engineer');
        $project  = $this->createProject($engineer);

        $module = Module::create([
            'project_id'     => $project->id,
            'domain'         => 'Payments',
            'name'           => 'Complete Module',
            'status'         => 'draft',
            'objective'      => 'Process payments',
            'inputs'         => ['payment_request'],
            'outputs'        => ['payment_confirmation'],
            'responsibility' => 'Payment Service',
        ]);

        $response = $this->actingAs($engineer)
            ->patchJson("/api/v1/projects/{$project->id}/modules/{$module->id}/validate");

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.status', 'validated');
    }
}