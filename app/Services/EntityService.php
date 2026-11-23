<?php

declare(strict_types=1);

namespace {

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
}

// Клас для роботи з об'єктами системи

namespace App\Services {

    use App\Interfaces\LoggerInterface;
    use App\Models\AbstractEntity;

    class EntityService
    {
        public function __construct(
            private LoggerInterface $logger
        ) {}

        public function processEntity(AbstractEntity $entity): string
        {
            $details = $entity->getDetails();

            $this->logger->log(
                "Оброблено об'єкт: {$details}"
            );

            return $details;
        }
    }
}
