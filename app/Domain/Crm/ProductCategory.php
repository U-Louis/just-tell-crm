<?php

namespace App\Domain\Crm;

final class ProductCategory
{
    /**
     * @var list<string>
     */
    public const VALUES = [
        'Hardware',
        'Software',
        'Services',
        'Subscription',
        'Other',
    ];

    public static function optional(?string $value, string $featureId): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (! in_array($value, self::VALUES, true)) {
            throw new CrmException('product.category.invalid', $featureId, [
                'category' => 'product.category.invalid',
            ]);
        }

        return $value;
    }
}
