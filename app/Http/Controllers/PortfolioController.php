<?php

namespace App\Http\Controllers;

use App\Portfolio;
use App\Stock;

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

    private function modifyPortfolio(Stock $stock, int $change)
    {
        // prepare a query of all stocks in a portfolio, see if the given stock
        // exists in that list
        $stocksQuery = $this->portfolio()->stocks();
        $stockPivot = $stocksQuery->where('stock_id', $stock->id);
        if ($stockPivot->exists()) {
            // the stock is already in the users portfolio, change the number of
            // shares by the given amount. Remove the stock if they sold all
            // of the shares they have.
            $stockPivot = $stockPivot->first();
            $newShares = $stockPivot->pivot->shares + $change;
            if ($newShares > 0) {
                $stocksQuery->updateExistingPivot($stockPivot->id, ['shares' => $newShares]);
            } else {
                $stocksQuery->detach($stock->id);
            }
        } elseif ($change > 0) {
            // the stock was not in the users portfolio and they bought shares..
            // add it to their portfolio with the given amount of shares.
            $stocksQuery->attach($stock->id, ['shares' => $change]);
        }
    }

    public function addStock(Stock $stock, int $amount)
    {
        $this->modifyPortfolio($stock, $amount);
    }

    public function removeStock(Stock $stock, int $amount)
    {
        $this->modifyPortfolio($stock, -$amount);
    }
}
