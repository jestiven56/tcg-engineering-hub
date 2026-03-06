<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesTestData;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_viewer_cannot_edit_modules(): void
    {
        $admin   = $this->createUser('admin');
        $viewer  = $this->createUser('viewer');
        $project = $this->createProject($admin);

        $module = Module::create([
            'project_id' => $project->id,
            'domain'     => 'Payments',
            'name'       => 'Some Module',
            'status'     => 'draft',
        ]);

        $response = $this->actingAs($viewer)
            ->putJson("/api/v1/projects/{$project->id}/modules/{$module->id}", [
                'name' => 'Hacked name',
            ]);

        $response->assertStatus(403);
    }

    public function test_viewer_cannot_edit_artifacts(): void
    {
        $admin   = $this->createUser('admin');
        $viewer  = $this->createUser('viewer');
        $project = $this->createProject($admin);

        $artifact = $project->artifacts()->first();

        $response = $this->actingAs($viewer)
            ->putJson("/api/v1/projects/{$project->id}/artifacts/{$artifact->id}", [
                'content_json' => ['transformation' => 'Hacked'],
            ]);

        $response->assertStatus(403);
    }

    public function test_viewer_cannot_validate_modules(): void
    {
        $admin   = $this->createUser('admin');
        $viewer  = $this->createUser('viewer');
        $project = $this->createProject($admin);

        $module = Module::create([
            'project_id'     => $project->id,
            'domain'         => 'Payments',
            'name'           => 'Some Module',
            'status'         => 'draft',
            'objective'      => 'Process payments',
            'inputs'         => ['input1'],
            'outputs'        => ['output1'],
            'responsibility' => 'Payment Service',
        ]);

        $response = $this->actingAs($viewer)
            ->patchJson("/api/v1/projects/{$project->id}/modules/{$module->id}/validate");

        $response->assertStatus(403);
    }

    public function test_engineer_can_edit_modules(): void
    {
        $admin    = $this->createUser('admin');
        $engineer = $this->createUser('engineer');
        $project  = $this->createProject($admin);

        $module = Module::create([
            'project_id' => $project->id,
            'domain'     => 'Payments',
            'name'       => 'Some Module',
            'status'     => 'draft',
        ]);

        $response = $this->actingAs($engineer)
            ->putJson("/api/v1/projects/{$project->id}/modules/{$module->id}", [
                'name' => 'Updated name',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }
}