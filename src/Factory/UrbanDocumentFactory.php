<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\UrbanDocument;

/**
 * @author mihani <maud.remoriquet@gmail.com>
 */
class UrbanDocumentFactory
{
    public static function create(string $name, string $archiveLink, string $status, string $type, string $urbanPartalId, \DateTime $apiUpdatedAt, \DateTime $uploadedAt): UrbanDocument
    {
        return (new UrbanDocument())
            ->setUrbanPortalId($urbanPartalId)
            ->setName($name)
            ->setStatus($status)
            ->setType($type)
            ->setArchiveLink($archiveLink)
            ->setApiUpdatedAt($apiUpdatedAt)
            ->setUploadedAt($uploadedAt)
        ;
    }

    public static function addUrbanFilesFromFilesMetaData(UrbanDocument $urbanDocument, array $files): UrbanDocument
    {
        foreach ($files as $file) {
            $urbanDocument->addUrbanFile(UrbanFileFactory::create($file['name'], $file['link']));
        }

        return $urbanDocument;
    }
}
