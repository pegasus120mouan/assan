<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $requested,
        public readonly int $available,
        string $message = 'Stock insuffisant.',
    ) {
        parent::__construct($message);
    }
}
