<?php

namespace App\Http\Controllers;

use App\Stock;
use App\Ticker;
use App\User;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    private function ensureWatchlistExists(User $user)
    {
        if (!$user->watchlist) {
            $user->watchlist()->create(['user_id' => $user->id]);
        }
    }

    private function executeAction($symbol, $action)
    {
        $user = Auth::user();
        if ($user instanceof User && Ticker::symbolExists($symbol)) {
            $stock = Stock::fetch($symbol);
            $this->ensureWatchlistExists($user);
            $user->watchlist->stocks()->$action($stock);

            return true;
        }

        return false;
    }

    public function add($symbol)
    {
        if ($this->executeAction($symbol, 'attach')) {
            return response("Successfully added $symbol to your watchlist", 200);
        }

        return response("Failed to add $symbol to your watchlist", 400);
    }

    public function remove($symbol)
    {
        if ($this->executeAction($symbol, 'detach')) {
            return response("Successfully removed $symbol from your watchlist", 200);
        }

        return response("Failed to remove $symbol from your watchlist", 400);
    }
}
