<?php

declare(strict_types=1);

namespace App\Tasks;

class Artist
{
    public function __construct(
        private int $id,
        private string $name,
        private bool $isBand,
        private int $startYear
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDetails(): string
    {
        $type = $this->isBand ? 'Гурт' : 'Сольний виконавець';

        return "{$this->name} ({$type}), початок діяльності: {$this->startYear}";
    }
}
