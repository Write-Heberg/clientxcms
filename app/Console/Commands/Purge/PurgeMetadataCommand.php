<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Purge;

use App\Models\Account\Customer;
use App\Models\Metadata;
use Illuminate\Console\Command;
use Schema;

class PurgeMetadataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:purge-metadata {batchSize=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Internal command to fix special characters in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing special characters in the database...');

        $this->fixSpecialChars();

        $this->purgeMetadata();

        $this->info('Special characters fixed successfully.');
    }

    private function fixSpecialChars()
    {
        $customers = Customer::all();
        /** @var Customer $customer */
        foreach ($customers as $customer){
            $customer->firstname = htmlspecialchars($customer->firstname, ENT_QUOTES);
            $customer->lastname = htmlspecialchars($customer->lastname, ENT_QUOTES);
            $customer->email = htmlspecialchars($customer->email, ENT_QUOTES);
            $customer->address = htmlspecialchars($customer->address, ENT_QUOTES);
            $customer->city = htmlspecialchars($customer->city, ENT_QUOTES);
            $customer->region = htmlspecialchars($customer->region, ENT_QUOTES);
            $customer->zipcode = htmlspecialchars($customer->zipcode, ENT_QUOTES);
            $customer->country = htmlspecialchars($customer->country, ENT_QUOTES);
            $customer->phone = htmlspecialchars($customer->phone, ENT_QUOTES);
            $customer->save();
        }
    }

    private function purgeMetadata()
    {
        $batchSize = $this->argument('batchSize');
        $this->info('Starting the purge of unused metadata...');


        Metadata::chunkById($batchSize, function ($metadataBatch) {
            $deletedCount = 0;
            foreach ($metadataBatch as $metadata) {
                if (!$this->modelExists($metadata->model_type, $metadata->model_id)) {
                    $metadata->delete();
                    $deletedCount++;
                }
            }

            $this->info("Deleted $deletedCount unused metadata records in this batch.");
        });

        $this->info('Unused metadata purged successfully.');
        return 0;

    }


    protected function modelExists($modelClass, $modelId)
    {
        if (!class_exists($modelClass)) {
            return false;
        }

        $model = new $modelClass;

        if (!Schema::hasTable($model->getTable())) {
            return false;
        }

        return $modelClass::where('id', $modelId)->exists();
    }
}
