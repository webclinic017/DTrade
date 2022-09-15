<?php

use App\Stock;
use Illuminate\Database\Seeder;

class StocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $desiredTickers = [
            'aapl',
            'amd',
            'sbux',
            'tsla',
            'msft',
            'goog',
            'pbr',
            'ibm',
            'ge',
            'gpro',
            'wti',
            'clf',
            'hmy',
        ];
        foreach ($desiredTickers as $ticker) {
            Stock::fetch($ticker);
            sleep(12);
        }
    }
}
