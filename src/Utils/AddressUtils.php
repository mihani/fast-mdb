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
        return self::inlineFormatFromDvfEntry(
            $address['lane']['number'],
            $address['lane']['btq'],
            $address['lane']['type'],
            $address['lane']['name'],
            $address['postal_code'],
            $address['city']['name']
        );
    }

    public static function inlineFormatFromDvfEntry(string $number, string $btq, string $type, string $name, string $postalCode, string $city): string
    {
        $inlineAddress = sprintf(
            "%s%s %s %s - %s %s",
            $number,
            $btq,
            strtolower($type),
            ucfirst(strtolower($name)),
            ucfirst(strtolower($city)),
            $postalCode
        );

        return preg_replace('/\s+/', ' ', trim($inlineAddress));
    }

    public static function inlineFormatFromEntity(string $addressLine1, string $addressLine2, string $postalCode, string $city): string
    {
        if ($addressLine1 != '' || $addressLine2 != '') {
            $inlineAddress = sprintf(
                '%s %s - %s %s',
                $addressLine1,
                $addressLine2,
                $city,
                $postalCode
            );
        } else {
            $inlineAddress = sprintf(
                '%s %s',
                $city,
                $postalCode
            );
        }

        return preg_replace('/\s+/', ' ', trim($inlineAddress));
    }
}
