<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands;

use App\Addons\Discordlink\Controllers\DiscordlinkController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateSwaggerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:create-swagger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create a swagger file for the clientxcms project.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        app('extension')->autoload(app(), false);
        Artisan::call('l5-swagger:generate');
    }
}
