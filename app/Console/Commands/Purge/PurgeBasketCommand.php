<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Console\Commands\Purge;

use App\Models\Store\Basket\Basket;
use Illuminate\Console\Command;

class PurgeBasketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientxcms:purge-basket {batchSize=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge unused basket records from the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Purging unused basket records from the database...');

        $this->purgeBasket();

        $this->info('Basket records purged successfully.');
    }

    private function purgeBasket()
    {
        $batchSize = $this->argument('batchSize');
        $this->info('Purging basket records in batches of ' . $batchSize . '...');
        $limit = now()->subWeeks(2);

        $baskets = Basket::where('created_at', '<', $limit)->whereNull('completed_at')->whereNull('user_id')->get();
        $nb = $baskets->count();
        $count = 0;
        $this->info('Found ' . $nb . ' basket records to purge.');
        foreach ($baskets as $basket) {
            if ($basket->rows->count() == 0) {
                $basket->delete();
                $count++;
            }
        }
        $this->info('Purged ' . $count . ' basket records.');
    }
}
