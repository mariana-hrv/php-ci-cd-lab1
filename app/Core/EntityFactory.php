<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AbstractEntity;
use App\Models\LegacyUser;
use App\Models\Property;
use InvalidArgumentException;

class EntityFactory
{
    public function createEntity(string $type, array $data): AbstractEntity
    {
        return match ($type) {
            'property' => new Property(
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
