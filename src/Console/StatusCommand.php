<?php

namespace Ridho\JustSubs\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class StatusCommand extends Command
{
    protected $signature = 'justsubs:status';

    protected $description = 'Show JustSubs installation status';

    public function handle()
    {
        $this->info('JustSubs Diagnostic Status');
        $this->line('--------------------------');

        $this->line('Config loaded: <info>Yes</info>');
        $this->line('Dashboard Prefix: <comment>'.Config::get('justsubs.route.prefix').'</comment>');
        $this->line('Configured Middleware: <comment>'.implode(', ', Config::get('justsubs.route.middleware', [])).'</comment>');

        $this->line('--------------------------');

        $tables = Config::get('justsubs.tables', []);
        foreach ($tables as $key => $tableName) {
            $exists = Schema::hasTable($tableName) ? '<info>Found</info>' : '<error>Missing</error>';
            $this->line(ucfirst($key)." Table ({$tableName}): {$exists}");
        }
    }
}
