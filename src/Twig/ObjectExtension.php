<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ObjectExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('instanceOf', [$this, 'instanceOf']),
        ];
    }

    public function instanceOf($object, $class)
    {
        if (!is_object($object)) {
            return false;
        }

        return $object instanceof $class;
    }
}