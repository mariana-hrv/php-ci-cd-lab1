<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\ExportableInterface;
use App\Traits\JsonExportTrait;

class LegacyProperty extends AbstractEntity implements ExportableInterface
{
    use JsonExportTrait;

    public function __construct(
        string $id,
        string $createdAt,
        private string $address,
        private float $price
    ) {
        parent::__construct($id, $createdAt);
    }

    public function getDetails(): string
    {
        return "Нерухомість: {$this->address}, ціна: {$this->price} грн";
    }
}
