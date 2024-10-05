<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Install;

use App\Models\Admin\Admin;
use Illuminate\Console\Command;

class UpdateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:update-admin {--email=} {--password=} {--firstname=} {--lastname=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the admin user for the clientxcms application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('firstname') && $this->option('lastname')) {
            $username = $this->option('firstname') . ' ' . $this->option('lastname');
        } else {
            $username = $this->ask('Admin username');
        }
        $data = [
            'username' => $username,
            'email' => $this->option('email') ?? $this->ask('Admin email'),
            'firstname' => $this->option('firstname') ?? $this->ask('Admin firstname'),
            'lastname' => $this->option('lastname') ?? $this->ask('Admin lastname'),
        ];
        if (!empty($this->option('password'))) {
            $data['password'] = bcrypt($this->option('password'));
        }
        Admin::first()->update($data);
        $this->info('Admin user updated successfully.');
    }
}
