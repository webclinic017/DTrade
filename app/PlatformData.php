<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlatformData extends Model
{
    protected $table = 'platform_data';
    protected $fillable = ['platform', 'username', 'password'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portfolio()
    {
        return $this->hasOne(Portfolio::class);
    }

    public function cookies()
    {
        return $this->hasOne(PlatformCookies::class);
    }
}
