<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlatformCookies extends Model
{
    protected $fillable = ['data'];

    public function platform()
    {
        return $this->belongsTo(PlatformData::class);
    }
}
