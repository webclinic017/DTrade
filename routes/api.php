<?php

use App\Jobs\Robinhood\JobTest;
use App\Jobs\Robinhood\RefreshPortfolioJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use RadicalLoop\Eod\Api\Stock as Market;
use RadicalLoop\Eod\Config as EodConfig;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('stock/{ticker}/price', function (Request $request, $ticker) {
    $market = new Market(new EodConfig(env('EOD_API_KEY')));
    $stock = $market->realTime("$ticker.US");

    return $stock->json();
});

Route::prefix('user')->name('user.')->middleware('auth:api')->group(function () {
    Route::get('/', function (Request $request) {
        return $request->user();
    });

    Route::prefix('{user}')->group(function () {
        Route::prefix('robinhood')->name('robinhood')->group(function () {
            Route::post('mfa', 'RobinhoodController@receiveMfaCode');
            Route::post('refreshPortfolio', 'RobinhoodController@refreshPortfolio');
        });
    });
});

Route::prefix('stocks')->name('stocks.')->middleware('auth:api')->group(function () {
    Route::prefix('{symbol}')->group(function () {
        Route::get('exists', 'StocksController@exists');
        Route::post('refresh', 'StocksController@refresh');
        Route::post('analyze', 'StocksController@analyze');
    });
});

Route::prefix('watchlist')->name('watchlist.')->middleware('auth:api')->group(function () {
    Route::prefix('stocks/{symbol}')->group(function () {
        Route::post('add', 'WatchlistController@add');
        Route::post('remove', 'WatchlistController@remove');
    });
});

Route::prefix('automation')->name('automation.')->middleware('auth:api')->group(function () {
    Route::prefix('position-balancing')->name('balancing.')->group(function () {
        Route::prefix('stock/{symbol}')->group(function () {
            Route::post('enable', 'AutomationController@enable');
            Route::post('disable', 'AutomationController@disable');
        });
    });
    Route::prefix('portfolio')->name('portfolio.')->group(function () {
        Route::prefix('stock/{symbol}')->group(function () {
            Route::post('buy', 'PortfolioController@buy');
            Route::post('sell', 'PortfolioController@sell');
        });
    });
});

Route::group(['middleware'=>'auth:api', 'prefix'=>'process/'], function () {
    Route::get('test', function (Request $request) {
        JobTest::dispatch(auth('api')->user());
    });

    Route::get('refresh', function (Request $request) {
        RefreshPortfolioJob::dispatch(auth('api')->user());
    });
});
