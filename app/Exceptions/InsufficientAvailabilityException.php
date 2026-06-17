<?php

namespace App\Exceptions;

use App\Models\Variant;
use RuntimeException;

class InsufficientAvailabilityException extends RuntimeException
{
    public function __construct(
        public readonly Variant $variant,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Insufficient availability for variant [%d] "%s": requested %d, only %d available.',
            $variant->id,
            $variant->label,
            $requested,
            $available,
        ));
    }
}