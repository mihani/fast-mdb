<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\String\u;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class StringUtils
{
    public static function slugify(string $string): string
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug(u($string)->lower()->toString())->toString();
    }
}
