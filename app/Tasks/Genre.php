<?php

declare(strict_types=1);

namespace App\Tasks;

class Genre
{
    private array $artists = [];

    public function __construct(
        private int $genreId,
        private string $name
    ) {}

    public function addArtist(Artist $artist): void
    {
        $this->artists[$artist->getId()] = $artist;
    }

    public function removeArtist(int $id): void
    {
        unset($this->artists[$id]);
    }

    public function searchArtist(string $name): ?Artist
    {
        foreach ($this->artists as $artist) {
            if ($artist->getName() === $name) {
                return $artist;
            }
        }

        return null;
    }

    public function getArtists(): array
    {
        return array_values($this->artists);
    }
}
