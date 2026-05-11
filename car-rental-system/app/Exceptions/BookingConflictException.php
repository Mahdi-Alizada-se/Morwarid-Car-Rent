<?php

namespace App\Exceptions;

use Exception;

class BookingConflictException extends Exception
{
    public function __construct(string $message = 'This vehicle is not available for the selected dates.')
    {
        parent::__construct($message);
    }
}