<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AbstractEntity;
use App\Models\LegacyProperty;
use App\Models\LegacyUser;
use InvalidArgumentException;

class EntityFactory
{
    public function createEntity(string $type, array $data): AbstractEntity
    {
        return match ($type) {
            'property' => new LegacyProperty(
                $data['id'],
                $data['createdAt'],
                $data['address'],
                $data['price']
            ),
            'user' => new LegacyUser(
                $data['id'],
                $data['createdAt'],
                $data['name'],
                $data['email']
            ),
            default => throw new InvalidArgumentException(
                "Невідомий тип об'єкта: {$type}"
            ),
        };
    }
}
