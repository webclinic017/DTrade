<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
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
        $user = Auth::user();
        if (!$user->watchlist) {
            $user->watchlist()->create(['user_id' => $user->id]);
        }

        return view('home', [
            'user'      => $user,
            'portfolio' => $user->portfolio,
            'watchlist' => $user->watchlist,
        ]);
    }
}
