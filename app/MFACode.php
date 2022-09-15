<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MFACode extends Model
{
    protected $table = 'mfa_codes';
    protected $fillable = ['code', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
