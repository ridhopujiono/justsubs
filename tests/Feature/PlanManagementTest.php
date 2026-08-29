<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ridho\JustSubs\JustSubs;
use Ridho\JustSubs\Tests\TestCase;
use Ridho\JustSubs\Models\Plan;
use Ridho\JustSubs\Enums\IntervalUnit;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironmentForTesting($app)
    {
        $app['config']->set('justsubs.route.middleware', ['web']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable authorization for tests unless specifically testing unauthorized access
        JustSubs::auth(function () { return true; });
    }

    protected function tearDown(): void
    {
        JustSubs::$authUsing = null;
        parent::tearDown();
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_can_list_plans()
    {
        Plan::factory()->create(['name' => 'Gold Plan']);
        Plan::factory()->create(['name' => 'Silver Plan']);

        $response = $this->get('/justsubs/plans');
        
        $response->assertStatus(200);
        $response->assertSee('Gold Plan');
        $response->assertSee('Silver Plan');
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_can_show_create_form()
    {
        $response = $this->get('/justsubs/plans/create');
        
        $response->assertStatus(200);
        $response->assertSee('Create Plan');
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_can_store_new_plan()
    {
        $response = $this->post('/justsubs/plans', [
            'name' => 'Platinum Plan',
            'slug' => 'platinum',
            'description' => 'Best plan ever',
            'price' => 150000,
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => 'month',
            'features' => "Feature 1\nFeature 2",
            'is_active' => '1',
        ]);

        $response->assertRedirect('/justsubs/plans');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('justsubs_plans', [
            'slug' => 'platinum',
            'price' => 150000,
            'is_active' => 1,
        ]);

        $plan = Plan::where('slug', 'platinum')->first();
        $this->assertEquals(['Feature 1', 'Feature 2'], $plan->features);
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_can_show_edit_form()
    {
        $plan = Plan::factory()->create(['name' => 'Bronze Plan']);

        $response = $this->get('/justsubs/plans/' . $plan->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertSee('Bronze Plan');
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_can_update_plan_and_deactivate()
    {
        $plan = Plan::factory()->create([
            'name' => 'Old Name',
            'is_active' => true,
            'slug' => 'old-name'
        ]);

        $response = $this->put('/justsubs/plans/' . $plan->id, [
            'name' => 'New Name',
            'slug' => 'old-name', // keep same slug
            'price' => 50000,
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => 'month',
            // Omit is_active to test deactivation
        ]);

        $response->assertRedirect('/justsubs/plans');
        
        $plan->refresh();
        $this->assertEquals('New Name', $plan->name);
        $this->assertFalse($plan->is_active);
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_validates_duplicate_slug()
    {
        Plan::factory()->create(['slug' => 'existing-slug']);

        $response = $this->post('/justsubs/plans', [
            'name' => 'New Plan',
            'slug' => 'existing-slug',
            'price' => 100,
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => 'month',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_validates_invalid_interval()
    {
        $response = $this->post('/justsubs/plans', [
            'name' => 'New Plan',
            'slug' => 'new-slug',
            'price' => 100,
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => 'decade', // invalid
        ]);

        $response->assertSessionHasErrors('interval_unit');
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_validates_invalid_money_amount()
    {
        $response = $this->post('/justsubs/plans', [
            'name' => 'New Plan',
            'slug' => 'new-slug',
            'price' => 10.5, // invalid, must be integer
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => 'month',
        ]);

        $response->assertSessionHasErrors('price');
        
        $response2 = $this->post('/justsubs/plans', [
            'name' => 'New Plan',
            'slug' => 'new-slug-2',
            'price' => -100, // invalid, must be min 0
            'currency' => 'IDR',
            'interval_count' => 1,
            'interval_unit' => 'month',
        ]);

        $response2->assertSessionHasErrors('price');
    }

    /**
     * @define-env defineEnvironmentForTesting
     */
    public function test_unauthorized_users_cannot_access()
    {
        // Remove the auth mock
        JustSubs::$authUsing = null;

        $this->get('/justsubs/plans')->assertStatus(403);
        $this->post('/justsubs/plans')->assertStatus(403);
    }
}
