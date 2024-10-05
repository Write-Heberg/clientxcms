<?php

namespace App\Console\Commands;

use App\Models\ActionLog;
use App\Models\ActionLogEntries;
use Illuminate\Console\Command;

class ClearSpamSettingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:clear-spam-action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = 4000;
        $actionlogQuery = ActionLogEntries::whereIn('attribute', ['app_license_refresh_token', 'app_cron_last_run'])->select('action_log_id');

        $this->info('Clearing spam action logs from the database...');

        $actionlogQuery->chunk($batchSize, function ($actionlog) {
            $actionLogIds = $actionlog->pluck('action_log_id');
            ActionLog::whereIn('id', $actionLogIds)->delete();

            $this->info('Processed batch of ' . count($actionLogIds) . ' action logs.');
        });

        $this->info('Finished clearing spam action logs from the database.');
    }
}
