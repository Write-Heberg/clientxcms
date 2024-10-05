<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Extension;

use Illuminate\Console\Command;

class CreateMigrationExtensionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:create-migration-extension {--model=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration extension for the CLIENTXCMS.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folders = [base_path('modules'), base_path('addons')];
        $extensions = [];
        foreach ($folders as $folder) {
            $directories = \File::directories($folder);
            foreach ($directories as $directory) {
                $extensions[] = basename($folder) . '/' . basename($directory) . '/database/migrations';
            }
        }
        $extension = $this->choice('Which extension do you want to create a migration for?', $extensions);
        $name = $this->ask('What is the name of the migration?');
        if ($this->option('model') == 'true') {
            $model = $this->ask('What is the name of the model?');
            $this->call('make:model', [
                'name' => $model,
                '--path' => $extension,
            ]);
        }
        \Artisan::call('make:migration', [
            'name' => $name,
            '--path' => $extension,
        ]);
        $this->comment(\Artisan::output());
    }
}
