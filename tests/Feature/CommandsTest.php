<?php

namespace Ridho\JustSubs\Tests\Feature;

use Illuminate\Support\Facades\File;
use Ridho\JustSubs\Tests\TestCase;

class CommandsTest extends TestCase
{
    public function test_install_command_publishes_assets_and_shows_next_steps()
    {
        // Clean up beforehand just in case
        if (File::exists(config_path('justsubs.php'))) {
            File::delete(config_path('justsubs.php'));
        }

        $this->artisan('justsubs:install')
            ->expectsOutput('Installing JustSubs...')
            ->expectsOutput('Publishing configuration...')
            ->expectsOutput('Publishing migrations...')
            ->expectsOutput('JustSubs scaffolding installed successfully.')
            ->expectsOutputToContain('Next steps:')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(config_path('justsubs.php')));
        
        // Clean up
        File::delete(config_path('justsubs.php'));
    }

    public function test_status_command_shows_diagnostics()
    {
        $this->artisan('justsubs:status')
            ->expectsOutput('JustSubs Diagnostic Status')
            ->expectsOutputToContain('Config loaded: Yes')
            ->expectsOutputToContain('Dashboard Prefix: justsubs')
            ->expectsOutputToContain('Configured Middleware: web, auth')
            ->assertExitCode(0);
    }
}
