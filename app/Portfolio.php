<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $appends = ['value', 'stocks'];
    protected $fillable = ['cash', 'user_id', 'platform_data_id'];

    public function getValueAttribute()
    {
        $value = $this->cash;
        $this->stocks->each(function (Stock $stock) use (&$value) {
            $value += $stock->value * $stock->pivot->shares;
        });

        return $value;
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class)->withPivot('shares');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platform()
    {
        return $this->belongsTo(PlatformData::class);
    }
}
