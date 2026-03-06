<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesTestData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GateOneTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_cannot_complete_domain_breakdown_if_big_picture_not_done(): void
    {
        $admin   = $this->createUser('admin');
        $project = $this->createProject($admin, 'discovery');

        // big_picture sigue en not_started (no lo tocamos)

        $artifact = $project->artifacts()
            ->where('type', 'domain_breakdown')
            ->first();

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/artifacts/{$artifact->id}/status", [
                'status' => 'done',
            ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false)
                 ->assertJsonFragment([
                     'Big Picture debe estar completado antes de finalizar Domain Breakdown.'
                 ]);
    }

    public function test_can_complete_domain_breakdown_if_big_picture_is_done(): void
    {
        $admin   = $this->createUser('admin');
        $project = $this->createProject($admin, 'discovery');

        // Marcar big_picture como done directamente en DB
        $this->setArtifactStatus($project, 'big_picture', 'done');

        $artifact = $project->artifacts()
            ->where('type', 'domain_breakdown')
            ->first();

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/artifacts/{$artifact->id}/status", [
                'status' => 'done',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }
}