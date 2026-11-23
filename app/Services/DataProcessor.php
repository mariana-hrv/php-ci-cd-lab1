<?php

declare(strict_types=1);

function shiftArrayRight(array $numbers): array
{
    if (empty($numbers)) {
        return [];
    }

    $lastElement = array_pop($numbers);
    array_unshift($numbers, $lastElement);

    return $numbers;
}

function getLastThreeWords(string $text): string
{
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

    $lastWords = array_slice($words, -3);

    return implode(' ', $lastWords);
}

function findMaxNumber(string $text): int
{
    $numbers = array_map('intval', explode(' ', trim($text)));

    return max($numbers);
}
