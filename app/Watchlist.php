<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $fillable = ['user_id'];

    public function stocks()
    {
        return $this->belongsToMany(Stock::class);
    }
}
