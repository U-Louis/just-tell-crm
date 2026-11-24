<?php

namespace App\Domain\Crm;

final class FieldText
{
    public static function optional(?string $value, int $max, string $code, string $featureId, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (mb_strlen($value) > $max) {
            throw new CrmException($code, $featureId, [$field => $code]);
        }

        return $value;
    }
}
