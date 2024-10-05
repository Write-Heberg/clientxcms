<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Install;

use App\Models\Admin\Admin;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LastLoggedAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:last-logged-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Table of the last logged admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Getting the last logged admin...');
        $admins = Admin::orderBy('last_login_ip', 'desc');
        $this->table(['ID', 'Username', 'Email', 'Last Logged At', 'Created At', 'Role'], $admins->get(['id', 'username', 'email', 'last_login_ip', 'created_at', 'role_id'])->toArray());
    }
}
