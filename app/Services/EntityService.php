<?php

declare(strict_types=1);

function updatePrice(float $currentPrice, float $changeAmount): float
{
    return $currentPrice + $changeAmount;
}

function applyDiscount(float $price, float $discount): float
{
    if ($discount < 0 || $discount > 100) {
        throw new Exception('Неприпустиме значення знижки!');
    }

    return $price - ($price * ($discount / 100));
}
