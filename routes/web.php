<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('home', 'HomeController@index')->name('home');

Route::prefix('stocks')->name('stocks.')->group(function () {
    Route::get('/', 'StocksController@index')->name('all');
    Route::prefix('{stock}')->group(function () {
        Route::get('/', 'StocksController@get')->name('stock');
        Route::get('detailed/{graph?}', 'StocksController@getDetailed')->name('stockDetailed');
    });
});

Route::prefix('portfolio')->name('portfolio.')->group(function () {
    Route::get('/', 'PortfolioController@get')->name('view');
    Route::prefix('stocks')->name('stocks.')->group(function () {
        Route::prefix('{stock}')->group(function () {
            Route::get('/', 'PortfolioController@getStock')->name('stock');
            Route::get('add/{amount}', 'PortfolioController@addStock')->name('add');
            Route::delete('remove/{amount}', 'PortfolioController@removeStock')->name('remove');
        });
    });
});

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', 'ProfileController@index')->name('index');
    Route::prefix('robinhood')->name('robinhood')->group(function () {
        Route::get('/', 'ProfileController@robinhood');
        Route::post('/', 'ProfileController@saveRobinhood')->name('.save');
    });
    Route::prefix('alpha-vantage')->name('alpha-vantage')->group(function () {
        Route::get('/', 'ProfileController@alphaVantage');
        Route::post('/', 'ProfileController@saveAlphaVantage')->name('.save');
    });
});

Auth::routes();
