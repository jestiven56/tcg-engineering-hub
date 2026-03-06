<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesTestData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectStatusGateTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_cannot_move_to_execution_if_required_artifacts_not_done(): void
    {
        $pm      = $this->createUser('pm');
        $project = $this->createProject($pm, 'discovery');

        // No marcamos ningún artefacto como done

        $response = $this->actingAs($pm)
            ->patchJson("/api/v1/projects/{$project->id}/status", [
                'status' => 'execution',
            ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false)
                 ->assertJsonFragment([
                     "El artefacto 'Strategic Alignment' debe estar completado (done)."
                 ]);
    }

    public function test_can_move_to_execution_if_all_required_artifacts_done(): void
    {
        $pm      = $this->createUser('pm');
        $project = $this->createProject($pm, 'discovery');

        // Marcar los 4 artefactos requeridos como done
        $this->setArtifactStatus($project, 'strategic_alignment', 'done');
        $this->setArtifactStatus($project, 'big_picture', 'done');
        $this->setArtifactStatus($project, 'domain_breakdown', 'done');
        $this->setArtifactStatus($project, 'module_matrix', 'done');

        $response = $this->actingAs($pm)
            ->patchJson("/api/v1/projects/{$project->id}/status", [
                'status' => 'execution',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.status', 'execution');
    }
}