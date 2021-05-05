<?php

declare(strict_types=1);

namespace App\Elasticsearch;

use App\Elasticsearch\Dto\ResponseDto;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Maud Remoriquet <maud.remoriquet@gmail.com>
 */
class ElasticsearchUtils
{
    public static function denormalizeResult(array $result): ResponseDto
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $serializer = new Serializer(
            [
                new ObjectNormalizer(
                    classMetadataFactory: $classMetadataFactory,
                    nameConverter: $metadataAwareNameConverter,
                    propertyTypeExtractor: new ReflectionExtractor()
                ),
                new ArrayDenormalizer(),
            ],
        );

        return $serializer->denormalize($result, ResponseDto::class);
    }
}
