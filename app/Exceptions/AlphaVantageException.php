<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AlphaVantageException extends Exception
{
    public function __construct($message = 'User has not registered an AV API Key', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return redirect(route('profile.alpha-vantage'));
    }
}
