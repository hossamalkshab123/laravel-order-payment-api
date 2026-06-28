<?php

namespace App\Core\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct(string $message = 'Unexpected error.', int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
