<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\SquareMeterPrice;

class SquareMeterPriceFactory
{
    public static function create(float $price, string $inseeCode, string $year)
    {
        return (new SquareMeterPrice())
            ->setPrice($price)
            ->setInseeCode($inseeCode)
            ->setYear($year)
        ;
    }
}
