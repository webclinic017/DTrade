<?php

namespace App\Jobs\Robinhood;

use App\Jobs\BrowserJob;

class JobTest extends BrowserJob
{
    /**
     * @throws \Exception
     */
    public function setup()
    {
        $orderDetails = [
            'order'         => 'buy',
            'order_type'    => 'limit',
            'ticker'        => 'dse',
            'shares'        => '1',
            'limit_price'   => '0.5',
            'expiration'    => 'Good till Canceled',
        ];

        $this->debug = true;
        $this->addTasks([
            new Tasks\LoginTask(),
            new Tasks\StockOrderTask($orderDetails),
            new Tasks\LogoutTask(),
        ]);
    }
}
