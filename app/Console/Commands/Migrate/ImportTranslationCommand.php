<?php

namespace App\Console\Commands\Migrate;

use File;
use Illuminate\Console\Command;
use ZipArchive;

class ImportTranslationCommand extends Command
{
    protected $signature = 'translations:import {--path=translations.zip}';
    protected $description = 'Import translations from a ZIP file and replace them in the project in PHP format';

    public function handle()
    {
        $zipFilePath = storage_path($this->option('path'));

        // Vérifier si le fichier ZIP existe
        if (!File::exists($zipFilePath)) {
            $this->error("Le fichier ZIP n'existe pas : {$zipFilePath}");
            return;
        }

        // Extraire le fichier ZIP
        $extractedPath = storage_path('app/translations_extracted');
        if (!File::exists($extractedPath)) {
            File::makeDirectory($extractedPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath) === TRUE) {
            $zip->extractTo($extractedPath);
            $zip->close();
            $this->info("Le fichier ZIP a été extrait avec succès.");
        } else {
            $this->error("Impossible d'ouvrir le fichier ZIP.");
            return;
        }

        // Parcourir les fichiers JSON extraits
        $jsonFiles = File::files($extractedPath . '/translations');
        foreach ($jsonFiles as $jsonFile) {
            $locale = basename($jsonFile, '.json');
            $this->info("Traitement du fichier : {$jsonFile}");

            // Charger le contenu JSON
            $translations = json_decode(File::get($jsonFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Erreur lors de la lecture du fichier JSON : {$jsonFile}");
                continue;
            }

            // Retirer la clé "language"
            unset($translations['language']);

            // Recréer la structure des fichiers PHP
            $this->processTranslations($translations, $locale);
        }

        // Supprimer les fichiers extraits
        File::deleteDirectory($extractedPath);
        $this->info("Importation terminée et fichiers extraits supprimés.");
    }

    protected function processTranslations($translations, $locale)
    {
        foreach ($translations as $path => $translationData) {
            // Déterminer le bon chemin à partir des clés
            $baseDir = base_path(str_replace('.', '/', $path));
            $fileName = basename($baseDir);

            // Créer le répertoire s'il n'existe pas
            $langDirectory = "{$baseDir}";
            if (!File::exists($langDirectory)) {
                File::makeDirectory($langDirectory, 0755, true);
            }

            // Construire le chemin du fichier PHP (par exemple, messages.php)
            $phpFilePath = "{$langDirectory}.php";

            // Convertir les traductions en format PHP
            $phpContent = "<?php\n\nreturn " . varExport($translationData, true) . ";\n";

            // Écrire le fichier PHP
            File::put($phpFilePath, $phpContent);
            $this->info("Fichier créé : {$phpFilePath}");

        }
    }

}
