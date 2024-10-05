<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands;

use App\Providers\AppServiceProvider;
use Illuminate\Console\Command;

class ClientxcmsUpdateVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:update-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the version of the CLIENTXCMS application.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating the version of the CLIENTXCMS application...');
        file_put_contents(storage_path('version'), "version=" . AppServiceProvider::VERSION . ';time=' . time());
        $this->info('The version of the CLIENTXCMS application has been updated.');
    }
}
