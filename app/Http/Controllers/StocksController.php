<?php

namespace App\Http\Controllers;

use App\Charts\Stocks\StockDataPoints;
use App\Charts\Stocks\StockIndicators;
use App\Charts\Stocks\StockPriceChart;
use App\Charts\Stocks\StockProjections;
use App\Events\UserActionCompleted;
use App\Events\UserActionFailed;
use App\Stock;
use App\StockProjection;
use App\Ticker;
use App\User;
use Illuminate\Support\Facades\Auth;

class StocksController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('pages.stocks.list', [
            'stocks'    => Stock::all(),
        ]);
    }

    private function getData($stock)
    {
        $stock = Stock::fetch($stock);
        $price = new StockPriceChart($stock);
        $projections = new StockProjections($stock);
        $indicators = new StockIndicators($stock);
        $portfolio = Auth::user()->portfolio;

        return [
            'portfolio' => $portfolio ? $portfolio : 'null',
            'stock'     => $stock,
            'charts'    => [
                'price'         => $price,
                'projections'   => $projections,
                'indicators'    => $indicators,
            ],
        ];
    }

    public function get($stock)
    {
        if (Ticker::symbolExists($stock)) {
            return view('pages.stocks.stock', $this->getData($stock));
        } else {
            return redirect(route('stocks.all'));
        }
    }

    public function getDetailed($stock, int $graph = 0)
    {
        $data = $this->getData($stock);
        $dataPoints = new StockDataPoints($data['stock'], $graph);
        $data['charts']['dataPoints'] = $dataPoints;

        return view('pages.stocks.stock', $data);
    }

    public function exists($symbol)
    {
        return Ticker::symbolExists($symbol);
    }

    public function refresh($symbol)
    {
        $user = Auth::user();
        $user = $user instanceof User ? $user : null;

        if (Ticker::symbolExists($symbol) && $user) {
            $ticker = Ticker::fetch($symbol);
            if ($ticker instanceof Ticker) {
                $ticker->updateData();
                event(new UserActionCompleted($user, 'Stock Refresh'));
            }
        } elseif ($user) {
            event(new UserActionFailed($user, 'Stock Refresh', 'Ticker Does not exist'));
        }
    }

    public function analyze($symbol)
    {
        $user = Auth::user();
        $user = $user instanceof User ? $user : null;

        if (Ticker::symbolExists($symbol) && $user) {
            $stock = Stock::fetch($symbol);
            StockProjection::makeFor($stock);
            event(new UserActionCompleted($user, 'Stock Analysis'));
        } elseif ($user) {
            event(new UserActionFailed($user, 'Stock Analysis', 'Ticker Does not exist'));
        }
    }
}
