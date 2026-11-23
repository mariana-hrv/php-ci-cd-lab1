<?php

declare(strict_types=1);
use Faker\Factory;

require_once __DIR__.'/app/Services/EntityService.php';
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/DataProcessor.php';

$currentPrice = 2500000.0;
$changeAmount = 100000.0;

$newPrice = updatePrice($currentPrice, $changeAmount);

echo '<h2>Оновлення ціни нерухомості</h2>';
echo '<p>Нова ціна: '.number_format($newPrice, 0, '.', ' ').' грн</p>';

$faker = Factory::create();

$prices = [];

for ($i = 0; $i < 10; $i++) {
    $prices[] = $faker->numberBetween(800000, 5000000);
}

$minPrice = 2000000;

$filteredPrices = array_filter($prices, function (int $price) use ($minPrice): bool {
    return $price > $minPrice;
});

echo '<h2>Згенеровані ціни нерухомості</h2>';
echo '<ul>';

foreach ($prices as $price) {
    echo '<li>'.number_format($price, 0, '.', ' ').' грн</li>';
}

echo '</ul>';

echo '<h2>Нерухомість дорожча за 2 000 000 грн</h2>';
echo '<ul>';

foreach ($filteredPrices as $price) {
    echo '<li>'.number_format($price, 0, '.', ' ').' грн</li>';
}

echo '</ul>';

echo '<h2>Обробка винятків</h2>';

try {
    $discountedPrice = applyDiscount(2000000.0, 150.0);

    echo '<p>Ціна зі знижкою: '.$discountedPrice.' грн</p>';
} catch (Exception $e) {
    echo '<p>Помилка: '.htmlspecialchars($e->getMessage()).'</p>';
} finally {
    echo '<p>Завершення спроби розрахунку.</p>';
}

echo '<h2>Завдання 5.1 — Циклічний зсув масиву</h2>';

$numbers = [10, 20, 30, 40, 50];
$shiftedNumbers = shiftArrayRight($numbers);

echo '<p>Початковий масив: '.implode(', ', $numbers).'</p>';
echo '<p>Після зсуву вправо: '.implode(', ', $shiftedNumbers).'</p>';

echo '<h2>Завдання 5.2 — Останні три слова рядка</h2>';

$text = 'Система дозволяє користувачам швидко знаходити нерухомість';

$lastThreeWords = getLastThreeWords($text);

echo '<p>Початковий рядок: '.htmlspecialchars($text).'</p>';
echo '<p>Останні три слова: '.htmlspecialchars($lastThreeWords).'</p>';

echo '<h2>Завдання 5.3 — Пошук максимального числа</h2>';

$numberText = '120 450 300 980 250';

$maxNumber = findMaxNumber($numberText);

echo '<p>Початковий рядок: '.htmlspecialchars($numberText).'</p>';
echo '<p>Максимальне число: '.$maxNumber.'</p>';
