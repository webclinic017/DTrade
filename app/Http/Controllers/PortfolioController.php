<?php

namespace App\Http\Controllers;

use App\Portfolio;
use App\Stock;
use Illuminate\Support\Facades\Request;

class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function portfolio(): Portfolio
    {
        $user = auth()->user();

        return $user->portfolio;
    }

    public function get()
    {
        return $this->portfolio();
    }

    public function addStock(Stock $stock, int $amount)
    {
        $this->portfolio()->modifyPortfolio($stock, $amount);
    }

    public function removeStock(Stock $stock, int $amount)
    {
        $this->portfolio()->modifyPortfolio($stock, -$amount);
    }

    public function buy($symbol)
    {
        $stock = Stock::fetch($symbol);
        $amount = Request::get('amount');
        $this->portfolio()->modifyPortfolio($stock, $amount);

        return response("Launched job to buy $symbol.");
    }

    public function sell($symbol)
    {
        $stock = Stock::fetch($symbol);
        $amount = Request::get('amount');
        $this->portfolio()->modifyPortfolio($stock, -$amount);

        return response("Launched job to sell $symbol.");
    }
}
