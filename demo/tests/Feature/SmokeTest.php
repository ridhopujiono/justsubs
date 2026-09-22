<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DemoSeeder']);
    }

    public function test_homepage_is_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('JustSubs');
    }

    public function test_login_demo_works()
    {
        $response = $this->get('/login-demo');
        $response->assertRedirect('/justsubs');
        $this->assertAuthenticated();
    }

    public function test_dashboard_is_accessible()
    {
        $user = User::where('email', 'admin@justsubs.test')->first();
        $response = $this->actingAs($user)->get('/justsubs');
        $response->assertStatus(200);
        $response->assertSee('Overview');
    }
}
