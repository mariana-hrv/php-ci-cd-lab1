<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

class QuadraticEquation
{
    public function __construct(
        private readonly float $a,
        private readonly float $b,
        private readonly float $c
    ) {
        if ($a == 0.0) {
            throw new InvalidArgumentException(
                'Коефіцієнт a не може дорівнювати нулю'
            );
        }
    }

    public function calculateDiscriminant(): float
    {
        return $this->b ** 2 - 4 * $this->a * $this->c;
    }

    public function calculateRoots(): array
    {
        $d = $this->calculateDiscriminant();

        if ($d < 0) {
            return [];
        }

        if ($d == 0) {
            return [-$this->b / (2 * $this->a)];
        }

        $x1 = (-$this->b + sqrt($d)) / (2 * $this->a);
        $x2 = (-$this->b - sqrt($d)) / (2 * $this->a);

        return [$x1, $x2];
    }
}
