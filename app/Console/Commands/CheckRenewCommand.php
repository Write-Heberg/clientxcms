<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckRenewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:check-renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check renew';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking renew...');
        $license = app('license')->getLicense(null, true);
        if (!$license->isValid()) {
            $this->info('License is invalid. Please renew your license.');
        } else {
            $this->info('License is valid. Thank you! New expiration date: ' . $license->get('expire'));
            try {
                unlink(storage_path('suspended'));
            } catch (\Exception $e) {
            }
        }
    }
}
