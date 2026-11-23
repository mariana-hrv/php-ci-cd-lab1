<?php

$propertyTypes = [
    'Квартира',
    'Будинок',
    'Земельна ділянка',
    'Комерційне приміщення',
    'Таунхаус',
];

sort($propertyTypes);

$property = [
    'id' => 1,
    'propertyType' => 'Квартира',
    'city' => 'Івано-Франківськ',
    'address' => 'вул. Незалежності, 25',
    'area' => 64.5,
    'rooms' => 2,
];

?>

<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <title>Об'єкти нерухомості</title>
</head>

<body>
    <h2>Типи нерухомості</h2>

    <ul>
        <?php foreach ($propertyTypes as $type) { ?>
            <li><?= htmlspecialchars($type) ?></li>
        <?php } ?>
    </ul>

    <h2>Характеристики об'єкта нерухомості</h2>

    <ul>
        <?php foreach ($property as $key => $value) { ?>
            <li>
                <?= htmlspecialchars((string) $key) ?>:
                <?= htmlspecialchars((string) $value) ?>
            </li>
        <?php } ?>
    </ul>
</body>

</html>