<?php

namespace App\Controller\Instructor;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Form\ChapitreType;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instructor/chapitre', name: 'instructor_chapitre_')]
class InstructorChapitreController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChapitreRepository $chapitreRepository,
        private ValidatorInterface $validator
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

        $errors = null;

            if ($form->isSubmitted()) {
                $violations = $this->validator->validate($chapitre);
                if (count($violations) > 0) {
                    $errors = $violations;
                } elseif ($form->isValid()) {
                    $this->em->persist($chapitre);
                    $this->em->flush();

                    $this->addFlash('success', 'Chapitre créé avec succès');

                    return $this->redirectToRoute('instructor_chapitre_index', ['coursId' => $coursId]);
                }
            }

        return $this->render('instructor/chapitre/form.html.twig', [
            'form' => $form,
            'chapitre' => $chapitre,
            'cours' => $cours,
            'isEdit' => false,
            'errors' => $errors ?? null,
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

        $errors = null;

        if ($form->isSubmitted()) {
            // content is mapped in the form now; validate entity
            $violations = $this->validator->validate($chapitre);
            if (count($violations) > 0) {
                $errors = $violations;
            } elseif ($form->isValid()) {
                $this->em->flush();
                $this->addFlash('success', 'Chapitre mis à jour avec succès');

                return $this->redirectToRoute('instructor_chapitre_index', ['coursId' => $cours->getId()]);
            }
        }

        return $this->render('instructor/chapitre/form.html.twig', [
            'form' => $form,
            'chapitre' => $chapitre,
            'cours' => $cours,
            'isEdit' => true,
            'errors' => $errors ?? null,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Chapitre $chapitre): Response
    {
        return $this->render('instructor/chapitre/show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(Chapitre $chapitre): Response
    {
        $html = $this->renderView('instructor/chapitre/pdf.html.twig', [
            'chapitre' => $chapitre,
        ]);

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdf = $dompdf->output();

            return new Response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="chapitre-' . $chapitre->getId() . '.pdf"',
            ]);
        }

        // Fallback: Dompdf not available — return clear 501 with instructions
        return new Response('PDF generation unavailable. Install dompdf/dompdf and run composer install.', 501, [
            'Content-Type' => 'text/plain',
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
