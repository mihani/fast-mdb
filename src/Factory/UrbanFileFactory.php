<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\UrbanFile;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class UrbanFileFactory
{
    public static function create(string $name, string $link): UrbanFile
    {
        return (new UrbanFile())
            ->setName($name)
            ->setLink($link);
    }
}
