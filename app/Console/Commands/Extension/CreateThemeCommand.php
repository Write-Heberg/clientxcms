<?php

namespace App\Console\Commands\Extension;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:create-theme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new theme for the CLIENTXCMS.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $name = $this->ask('What is the name of the theme?');
        $uuid = $this->ask('What is the UUID of the theme?');
        if (File::exists(resource_path("themes/$uuid"))) {
            $this->error('The theme already exists.');
            return;
        }
        $description = $this->ask('What is the description of the theme?');
        $this->info("Creating a new theme named $name...");
        File::makeDirectory(resource_path("themes/$uuid/views"), 0755, true, true);
        $author_name = $this->ask('What is the name of the author?');
        $author_email = $this->ask('What is the email of the author?');

        $this->info("Creating a new theme named $name...");
        File::put(resource_path("themes/$uuid/theme.json"), json_encode([
            'name' => $name,
            'uuid' => $uuid,
            'description' => $description,
            'version' => '1.0.0',
            'author' => [
                'name' => $author_name,
                'email' => $author_email,
            ],
        ], JSON_PRETTY_PRINT));
        $css = $this->confirm('Do you make a CSS file for this theme?', true);
        if ($css) {
            File::makeDirectory(resource_path("themes/$uuid/css"), 0755, true, true);
            File::put(resource_path("themes/$uuid/css/app.css"), "@tailwind base;
@tailwind components;
@tailwind utilities;
@import 'bootstrap-icons/font/bootstrap-icons.min.css';
@import 'flatpickr/dist/flatpickr.min.css';
/* Your CSS code here */");
        }
        $js = $this->confirm('Do you make a JS file for this theme?', true);
        File::makeDirectory(resource_path("themes/$uuid/js"), 0755, true, true);
        if ($js) {
            File::put(resource_path("themes/$uuid/js/app.js"), "import 'preline'
import.meta.glob([
    '/resources/global/**',
    '/resources/global/js/**',
]);
");
        }
        $config = $this->confirm('Do you make a config file for this theme?', true);
        if ($config) {
            File::makeDirectory(resource_path("themes/$uuid/config"), 0755, true, true);
            File::put(resource_path("themes/$uuid/config/config.php"), "<?php\n\nreturn [];");
            File::put(resource_path("themes/$uuid/config/config.json"), json_encode([], JSON_PRETTY_PRINT));
            File::put(resource_path("themes/$uuid/config/config.blade.php"), '');
            $this->info('Config created successfully.');
        }
        $lang = $this->confirm('Do you make a lang file for this theme?', true);
        if ($lang) {
            File::makeDirectory(resource_path("themes/$uuid/lang"), 0755, true, true);
            File::put(resource_path("themes/$uuid/lang/en/messages.php"), json_encode([], JSON_PRETTY_PRINT));
            File::put(resource_path("themes/$uuid/lang/fr/messages.php"), json_encode([], JSON_PRETTY_PRINT));
            $this->info('Lang created successfully.');
        }
        $this->copyDirectory(__DIR__ . '/stub/theme/views', resource_path("themes/$uuid/views"), $uuid);
        $this->info('Theme created successfully.');
    }

    public function copyDirectory(string $source, string $destination, string $uuid): bool
    {
        if (!File::isDirectory($source)) {
            return false;
        }
        if (!File::isDirectory($destination)) {
            File::makeDirectory($destination, 0755, true);
        }
        $files = File::files($source);
        $directories = File::directories($source);
        foreach ($files as $file) {
            $destFilePath = $destination . '/' . File::basename($file);
            File::copy($file, $destFilePath);
            $content = File::get($destFilePath);
            $content = str_replace('$THEME_NAME', $uuid, $content);
            File::put($destFilePath, $content);
        }

        foreach ($directories as $directory) {
            $destDirPath = $destination . '/' . File::basename($directory);
            $this->copyDirectory($directory, $destDirPath, $uuid);
        }

        return true;
    }
}
