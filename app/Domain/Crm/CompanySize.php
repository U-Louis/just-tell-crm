<?php

namespace App\Domain\Crm;

final class CompanySize
{
    /**
     * @var list<string>
     */
    public const VALUES = [
        '1-10',
        '11-50',
        '51-200',
        '201-1000',
        '1001+',
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
            throw new CrmException('contact.company_size.invalid', $featureId, [
                'company_size' => 'contact.company_size.invalid',
            ]);
        }

        return $value;
    }
}
