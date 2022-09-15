<?php

namespace App\Http\Controllers;

use App\Automation;
use App\Stock;
use Illuminate\Support\Facades\Auth;

class AutomationController extends Controller
{
    private function automationFor($symbol): Automation
    {
        $user = Auth::user();
        $stock = Stock::fetch($symbol);

        return $user->automations()->firstOrCreate(['stock_id' => $stock->id]);
    }

    public function enable($symbol)
    {
        $automation = $this->automationFor($symbol)->enable()->execute();

        return response($automation, 200);
    }

    public function disable($symbol)
    {
        $automation = $this->automationFor($symbol)->disable();

        return response($automation, 200);
    }
}
