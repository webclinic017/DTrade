<?php

namespace App\Http\Controllers;

use App\Jobs\Robinhood\RefreshPortfolioJob;
use App\User;
use Illuminate\Http\Request;

class RobinhoodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function receiveMfaCode(Request $request, User $user)
    {
        $code = $request->code;
        if ($code) {
            return response($user->mfaCode()->create(['code' => $code]), 200);
        }

        return response('Code not sent.', 400);
    }

    public function refreshPortfolio(User $user)
    {
        if ($user->portfolio) {
            RefreshPortfolioJob::dispatch($user);
        }
    }
}
