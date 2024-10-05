<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Install;

use App\Helpers\EnvEditor;
use Illuminate\Console\Command;

class InstallOauthClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:install-oauth-client {--client_id=} {--client_secret=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the oauth client for the clientxcms application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app('installer')->isEnvExists() && app('installer')->isEnvWritable()) {

            EnvEditor::updateEnv([
                'OAUTH_CLIENT_ID' => $this->option('client_id') ?? $this->ask('Oauth Client ID'),
                'OAUTH_CLIENT_SECRET' => $this->option('client_secret') ?? $this->secret('Oauth Client Secret'),
            ]);
            $this->info('Oauth Client installed successfully.');
        } else {
            $this->error('Env file is not configured.');
        }
    }
}
