<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

class Car extends Vehicle
{
    public function __construct(float $speed)
    {
        parent::__construct($speed);
    }

    public function calculateTravelTime(float $distance): float
    {
        if ($distance < 0) {
            throw new InvalidArgumentException(
                'Відстань не може бути від’ємною'
            );
        }

        return $distance / $this->speed;
    }
}
