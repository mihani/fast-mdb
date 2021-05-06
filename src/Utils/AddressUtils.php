<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
 */
class AddressUtils
{
    public static function inlineFormatAddressFromAddressDvfEntries(array $address): string
    {
        return self::inlineFormat(
            $address['lane']['number'],
            $address['lane']['btq'],
            $address['lane']['type'],
            $address['lane']['name'],
            $address['postal_code'],
            $address['city']['name']
        );
    }

    public static function inlineFormat(string $number, string $btq, string $type, string $name, string $postalCode, string $city): string
    {
        $inlineAddress = sprintf(
            '%s%s %s %s %s %s',
            $number,
            $btq,
            strtolower($type),
            ucfirst(strtolower($name)),
            $postalCode,
            ucfirst(strtolower($city))
        );

        return preg_replace('/\s+/', ' ', trim($inlineAddress));
    }
}
