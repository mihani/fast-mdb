<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Document;
use App\Security\Voter\DocumentVoter;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/document')]
class DocumentController extends AbstractController
{
    #[Route('/{id}/download', name: 'document_download')]
    public function download(Document $document, FilesystemOperator $documentsStorage): Response
    {
        $this->denyAccessUnlessGranted(DocumentVoter::COMPANY_VIEW, $document);

        $resource = $documentsStorage->readStream($document->getFile()->getName());

        if ($resource === false) {
            throw new FileNotFoundException(sprintf('Error opening stream for "%s"', $document->getFile()->getName()));
        }

        $response = new StreamedResponse(function () use ($resource) {
            $outputStream = fopen('php://output', 'wb');
            stream_copy_to_stream($resource, $outputStream);
        });
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $document->getFile()->getOriginalName());

        $response->headers->set('Content-Type', $document->getFile()->getMimeType());
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
