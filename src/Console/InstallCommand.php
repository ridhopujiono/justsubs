<?php

namespace Ridho\JustSubs\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'justsubs:install';
    protected $description = 'Install the JustSubs package';

    public function handle()
    {
        $this->info('Installing JustSubs...');

        $this->info('Publishing configuration...');
        $this->call('vendor:publish', ['--tag' => 'justsubs-config']);

        $this->info('Publishing migrations...');
        $this->call('vendor:publish', ['--tag' => 'justsubs-migrations']);

        $this->info('JustSubs scaffolding installed successfully.');
        $this->info('Next steps:');
        $this->info('1. Run `php artisan migrate` to create the tables.');
        $this->info('2. Configure the authorization gate in your AppServiceProvider (e.g. `JustSubs::auth(...)`).');
    }
}
