<?php

namespace App\Domain\Crm\Fixtures;

use App\Domain\Crm\ProductCategory;

final class ProductFactory
{
    public function __construct(
        public readonly string $name,
        public readonly int $unitPrice,
        public readonly string $description,
        public readonly string $category,
    ) {}

    public static function make(): self
    {
        $kind = self::pick(['Cable', 'Lamp', 'Ribbon', 'Bracket', 'Switch', 'Panel', 'Sensor', 'Adapter']);
        $name = $kind.' '.random_int(1000, 9999);
        $category = ProductCategory::VALUES[array_rand(ProductCategory::VALUES)];

        return new self(
            $name,
            random_int(1, 50),
            $name.' is a '.$category.' item for the catalog.',
            $category,
        );
    }

    /**
     * @param  list<string>  $values
     */
    private static function pick(array $values): string
    {
        return $values[array_rand($values)];
    }
}
