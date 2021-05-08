<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Address;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class AddressFactory
{
    public static function create(string $addressLine1, string $city, string $postalCode, string $inseeCode = null, float $latitude = null, float $longitude = null): Address
    {
        return (new Address())
            ->setAddressLine1($addressLine1)
            ->setCity($city)
            ->setPostalCode($postalCode)
            ->setInseeCode($inseeCode)
            ->setLatitude($latitude)
            ->setLongitude($longitude);
    }
}
