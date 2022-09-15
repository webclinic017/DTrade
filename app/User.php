<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['numberDayTrades'];

    public function platforms()
    {
        return $this->hasMany(PlatformData::class);
    }

    public function portfolio()
    {
        return $this->hasOne(Portfolio::class);
    }

    public function watchlist()
    {
        return $this->hasOne(Watchlist::class);
    }

    public function dataSource()
    {
        return $this->hasOne(AlphaVantageApi::class);
    }

    public function mfaCode()
    {
        return $this->hasmany(MFACode::class)->orderBy('created_at', 'desc')->limit(1);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    public function getNumberDayTradesAttribute()
    {
        return $this->trades()->thisWeek()->get()->filter(function (Trade $trade) {
            return $trade->isDayTrade();
        })->count();
    }
}
