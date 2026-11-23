<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

abstract class Vehicle
{
    public function __construct(
        protected float $speed
    ) {
        if ($speed <= 0) {
            throw new InvalidArgumentException(
                'Швидкість має бути більшою за нуль'
            );
        }
    }

    abstract public function calculateTravelTime(float $distance): float;
}
