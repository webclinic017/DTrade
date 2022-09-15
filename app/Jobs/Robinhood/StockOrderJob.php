<?php

namespace App\Jobs\Robinhood;

use App\Jobs\BrowserJob;
use App\User;
use Illuminate\Support\Facades\Log;

class StockOrderJob extends BrowserJob
{
    protected $orderDetails;

    public function __construct($orderDetails, User $user = null, array $tags = [])
    {
        $this->orderDetails = $orderDetails;
        Log::debug($orderDetails);
        parent::__construct($user, $tags);
    }

    /**
     * @throws \Exception
     */
    public function setup()
    {
        $this->debug = true;
        $this->addTasks([
            new Tasks\LoginTask(),
            new Tasks\StockOrderTask($this->orderDetails),
            new Tasks\LogoutTask(),
        ]);
    }
}
