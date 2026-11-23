<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\EntityFactory;
use App\Interfaces\ExportableInterface;
use App\Services\ConsoleLogger;
use App\Services\EntityService;
use App\Tasks\Artist;
use App\Tasks\Car;
use App\Tasks\ExpressTrain;
use App\Tasks\Genre;
use App\Tasks\QuadraticEquation;
use App\Tasks\Train;
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

echo '<h2>Лабораторна №4 — Патерн Singleton</h2>';

echo '<pre>';

$database1 = Database::getInstance();
$database2 = Database::getInstance();

echo 'Один і той самий екземпляр: '
    .($database1 === $database2 ? 'Так' : 'Ні');

echo '</pre>';

echo '<h2>Лабораторна №4 — Патерн Factory</h2>';

$factory = new EntityFactory;

$property = $factory->createEntity('property', [
    'id' => 'P001',
    'createdAt' => '2026-11-23',
    'address' => 'м. Івано-Франківськ',
    'price' => 2500000.0,
]);

$user = $factory->createEntity('user', [
    'id' => 'U001',
    'createdAt' => '2026-11-23',
    'name' => 'Марія',
    'email' => 'maria@example.com',
]);

echo '<p>'.htmlspecialchars($property->getDetails()).'</p>';
echo '<p>'.htmlspecialchars($user->getDetails()).'</p>';

echo '<h2>Лабораторна №4 — Експорт у JSON</h2>';

if ($property instanceof ExportableInterface) {
    echo '<pre>';
    echo htmlspecialchars(
        $property->exportToJson(),
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</pre>';
}

echo '<h2>Лабораторна №4 — Dependency Injection</h2>';

$logger = new ConsoleLogger;

$entityService = new EntityService($logger);

echo '<pre>';
$entityService->processEntity($property);
echo '</pre>';

echo '<h2>Варіант 9 — Композиція класів Artist і Genre</h2>';

$genre = new Genre(1, 'Рок');

$artist1 = new Artist(1, 'Queen', true, 1970);
$artist2 = new Artist(2, 'The Beatles', true, 1960);

$genre->addArtist($artist1);
$genre->addArtist($artist2);

echo '<h3>Список виконавців:</h3>';

foreach ($genre->getArtists() as $artist) {
    echo '<p>'.htmlspecialchars($artist->getDetails()).'</p>';
}

echo '<h3>Пошук виконавця:</h3>';

$foundArtist = $genre->searchArtist('Queen');

if ($foundArtist !== null) {
    echo '<p>Знайдено: '
        .htmlspecialchars($foundArtist->getDetails())
        .'</p>';
}

$genre->removeArtist(2);

echo '<h3>Список після видалення:</h3>';

foreach ($genre->getArtists() as $artist) {
    echo '<p>'.htmlspecialchars($artist->getDetails()).'</p>';
}

echo '<h2>Варіант 9 — Ієрархія транспортних засобів</h2>';

$distance = 300.0;

$car = new Car(80.0);
$train = new Train(120.0);
$expressTrain = new ExpressTrain;

$vehicles = [
    'Автомобіль' => $car,
    'Потяг' => $train,
    'Потяг-експрес' => $expressTrain,
];

echo '<p>Відстань: '.$distance.' км</p>';

foreach ($vehicles as $name => $vehicle) {
    $time = $vehicle->calculateTravelTime($distance);

    echo '<p>'.$name.': '
        .number_format($time, 2, '.', '')
        .' год</p>';
}

echo '<h2>Варіант 9 — Квадратні рівняння</h2>';

$equations = [
    'Два корені' => new QuadraticEquation(1, -5, 6),
    'Один корінь' => new QuadraticEquation(1, -2, 1),
    'Немає дійсних коренів' => new QuadraticEquation(1, 2, 5),
];

foreach ($equations as $name => $equation) {
    $d = $equation->calculateDiscriminant();
    $roots = $equation->calculateRoots();

    echo '<h3>'.$name.'</h3>';
    echo '<p>Дискримінант: '.$d.'</p>';

    if (empty($roots)) {
        echo '<p>Дійсних коренів немає</p>';
    } else {
        echo '<p>Корені: '.implode(', ', $roots).'</p>';
    }
}
