<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\Portfolio;
use App\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('jobs', function (User $user) {
    return $user->hasRole('admin');
});

Broadcast::channel('portfolio.{portfolio}', function (User $user, Portfolio $portfolio) {
    return $user->id === $portfolio->user->id;
});
