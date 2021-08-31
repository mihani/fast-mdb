<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Note;
use App\Entity\Project;
use App\Form\NoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/note')]
class ProjectNoteController extends AbstractController
{
    #[Route('/{project}', name: 'project_note_create', methods: ['POST'])]
    public function create(Project $project, Request $request, EntityManagerInterface $em)
    {
        $noteForm = $this->createForm(NoteType::class, new Note());
        $noteForm->handleRequest($request);

        if ($noteForm->isSubmitted() && $noteForm->isValid()) {
            $note = $noteForm->getData();
            $note->setProject($project)
                ->setAuthor($this->getUser()->getFullName().' - '.$this->getUser()->getEmail())
            ;
            $em->persist($note);
            $em->flush();
        }

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }

    #[Route('/{note}/edit', name: 'project_note_edit', methods: ['POST'])]
    public function edit(Note $note, Request $request, EntityManagerInterface $em)
    {
        $noteContent = $request->request->get('content');
        $note->setContent($noteContent);
        $note->setUpdatedAt(new \DateTime());

        $em->persist($note);
        $em->flush();

        return new JsonResponse();
    }

    #[Route('/{note}/delete', name: 'project_note_delete', methods: ['POST'])]
    public function delete(Note $note, EntityManagerInterface $em)
    {
        $em->remove($note);
        $em->flush();

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }

    #[Route('/{note}/modal-form', name: 'project_note_modal_form', methods: ['GET'])]
    public function modalForm(Note $note)
    {
        $noteForm = $this->createForm(NoteType::class, $note);

        return $this->render('note/form.html.twig', [
            'noteForm' => $noteForm->createView(),
        ]);
    }
}
