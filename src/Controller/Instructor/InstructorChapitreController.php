<?php

namespace App\Controller\Instructor;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Form\ChapitreType;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instructor/chapitre', name: 'instructor_chapitre_')]
class InstructorChapitreController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChapitreRepository $chapitreRepository
    ) {}

    #[Route('/cours/{coursId}', name: 'index', methods: ['GET'])]
    public function index(int $coursId): Response
    {
        $cours = $this->em->getRepository(Cours::class)->find($coursId);
        if (!$cours) {
            throw $this->createNotFoundException('Cours not found');
        }

        $instructorName = 'instructor_user1';

        // Check authorization: can only view own course chapters
        if ($cours->getCreatedBy() !== $instructorName && $cours->getStatus() !== 'APPROVED') {
            throw $this->createAccessDeniedException('You can only manage chapters of your own courses');
        }

        $chapitres = $this->chapitreRepository->findByCours($coursId);

        return $this->render('instructor/chapitre/index.html.twig', [
            'cours' => $cours,
            'chapitres' => $chapitres,
        ]);
    }

    #[Route('/create/{coursId}', name: 'create', methods: ['GET', 'POST'])]
    public function create(int $coursId, Request $request): Response
    {
        $cours = $this->em->getRepository(Cours::class)->find($coursId);
        if (!$cours) {
            throw $this->createNotFoundException('Cours not found');
        }

        $instructorName = 'instructor_user1';

        // Can only create chapters for own courses
        if ($cours->getCreatedBy() !== $instructorName) {
            throw $this->createAccessDeniedException('You can only create chapters for your own courses');
        }

        $chapitre = new Chapitre();
        $chapitre->setCours($cours);

        $form = $this->createForm(ChapitreType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle content JSON conversion
            $content = $request->request->get('content');
            if ($content) {
                $chapitre->setContent(json_decode($content, true) ?? []);
            }

            $this->em->persist($chapitre);
            $this->em->flush();

            $this->addFlash('success', 'Chapitre créé avec succès');

            return $this->redirectToRoute('instructor_chapitre_index', ['coursId' => $coursId]);
        }

        return $this->render('instructor/chapitre/form.html.twig', [
            'form' => $form,
            'chapitre' => $chapitre,
            'cours' => $cours,
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Chapitre $chapitre, Request $request): Response
    {
        $cours = $chapitre->getCours();
        $instructorName = 'instructor_user1';

        // Authorization check
        if ($cours->getCreatedBy() !== $instructorName) {
            throw $this->createAccessDeniedException('You can only edit chapters of your own courses');
        }

        $form = $this->createForm(ChapitreType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle content JSON conversion
            $content = $request->request->get('content');
            if ($content) {
                $chapitre->setContent(json_decode($content, true) ?? []);
            }

            $this->em->flush();
            $this->addFlash('success', 'Chapitre mis à jour avec succès');

            return $this->redirectToRoute('instructor_chapitre_index', ['coursId' => $cours->getId()]);
        }

        return $this->render('instructor/chapitre/form.html.twig', [
            'form' => $form,
            'chapitre' => $chapitre,
            'cours' => $cours,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Chapitre $chapitre): Response
    {
        return $this->render('instructor/chapitre/show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Chapitre $chapitre, Request $request): Response
    {
        $cours = $chapitre->getCours();
        $instructorName = 'instructor_user1';

        if ($cours->getCreatedBy() !== $instructorName) {
            throw $this->createAccessDeniedException('You can only delete chapters of your own courses');
        }

        if ($this->isCsrfTokenValid('delete' . $chapitre->getId(), $request->request->get('_token'))) {
            $this->em->remove($chapitre);
            $this->em->flush();
            $this->addFlash('success', 'Chapitre supprimé avec succès');
        }

        return $this->redirectToRoute('instructor_chapitre_index', ['coursId' => $cours->getId()]);
    }
}
