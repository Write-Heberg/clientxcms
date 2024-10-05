<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Extension;

use Illuminate\Console\Command;
use Throwable;

class DatabaseExtensionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:db-extension {--action=migrate} {--extension=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the migrations folder of a extension.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folders = [base_path('modules'), base_path('addons')];
        $extensions = [];
        $extension = null;
        foreach ($folders as $folder) {
            $directories = \File::directories($folder);
            foreach ($directories as $directory) {
                if (app('extension')->extensionIsEnabled(basename($directory))){
                    $extensions[] = basename($folder) . '/' . basename($directory) . '/database/migrations';
                }
                if ($this->option('extension') == basename($directory)) {
                    $extension = basename($folder) . '/' . basename($directory) . '/database/migrations';
                }
            }
        }
        if ($this->hasOption('all') && $this->option('all')) {
            foreach ($extensions as $extension) {
                try {
                    \Artisan::call('migrate', [
                        '--force' => true,
                        '--path' => $extension,
                    ]);
                    $this->comment(\Artisan::output());
                } catch (Throwable $e){
                }
            }
            return;
        }
        if (empty($extensions)) {
            $this->error('No extensions found in the modules or addons folder.');
            return;
        }
        if ($extension == null){
            $extension = $this->choice('Which extension do you want to create a migration for?', $extensions);
        }
        if ($this->option('action') == 'migrate') {
            $this->migrate($extension);
        } elseif ($this->option('action') == 'rollback') {
            $this->rollback($extension);
        } else if($this->option('action') == 'seed') {
            $this->seed($extension);
        } else {
            $this->error('Invalid action. Available actions are migrate, rollback and seed.');
        }
    }

    private function migrate($extension)
    {
        $this->info('Migrating extension: ' . $extension);
        \Artisan::call('migrate', ['--path' => $extension, '--force' => true]);
        $this->info(\Artisan::output());
        $this->info('Extension migrated successfully.');
    }

    private function rollback($extension)
    {
        $this->info('Rolling back extension: ' . $extension);
        \Artisan::call('migrate:rollback', ['--path' => $extension, '--force' => true]);
        $this->info(\Artisan::output());
        $this->info('Extension rolled back successfully.');
    }

    private function seed($extension)
    {
        $this->info('Seeding extension: ' . $extension);
        \Artisan::call('db:seed', ['--path' => $extension, '--force' => true]);
        $this->info(\Artisan::output());
        $this->info('Extension seeded successfully.');
    }
}
