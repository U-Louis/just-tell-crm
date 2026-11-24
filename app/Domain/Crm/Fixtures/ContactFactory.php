<?php

namespace App\Domain\Crm\Fixtures;

use App\Domain\Crm\Column;
use App\Domain\Crm\CompanySize;

final class ContactFactory
{
    public const COLUMN = Column::NEW;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $company,
        public readonly string $description,
        public readonly string $notes,
        public readonly string $companySize,
        public readonly int $ownerId,
    ) {}

    public static function make(int $ownerId): self
    {
        $given = self::pick(['Alex', 'Jordan', 'Sam', 'Riley', 'Casey', 'Morgan', 'Quinn', 'Avery']);
        $family = self::pick(['Works', 'Supply', 'Studio', 'Goods', 'Labs', 'Trading', 'Partners', 'North']);
        $place = self::pick(['Harbor', 'Maple', 'Cedar', 'Lumen', 'Pioneer', 'Summit', 'River', 'Bright']);
        $name = $given.' '.$family;
        $company = $place.' '.$family;
        $tag = random_int(100, 999);

        return new self(
            $name,
            strtolower($given.'.'.$family.'.'.$tag).'@example.com',
            $company,
            $company.' asked for a first conversation about a new order.',
            'Imported fixture. Follow up while the column is still new.',
            CompanySize::VALUES[array_rand(CompanySize::VALUES)],
            $ownerId,
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
