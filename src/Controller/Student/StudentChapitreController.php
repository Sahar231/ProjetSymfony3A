<?php

namespace App\Controller\Student;

use App\Entity\Cours;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/student/chapitre', name: 'student_chapitre_')]
class StudentChapitreController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChapitreRepository $chapitreRepository,
        private ValidatorInterface $validator
    ) {}

    // View chapters of an approved course (read-only)
    #[Route('/cours/{coursId}', name: 'index')]
    public function index(int $coursId): Response
    {
        $cours = $this->em->getRepository(Cours::class)->find($coursId);

        if (!$cours || $cours->getStatus() !== 'APPROVED') {
            throw $this->createNotFoundException('Course not found or not approved');
        }

        $chapitres = $this->chapitreRepository->findByCours($coursId);

        return $this->render('student/chapitre/index.html.twig', [
            'cours' => $cours,
            'chapitres' => $chapitres,
        ]);
    }

    // View chapter content (read-only)
    #[Route('/{id}', name: 'show')]
    public function show(int $id): Response
    {
        $chapitre = $this->chapitreRepository->find($id);

        if (!$chapitre || $chapitre->getCours()->getStatus() !== 'APPROVED') {
            throw $this->createNotFoundException('Chapter not found or course not approved');
        }

        return $this->render('student/chapitre/show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(int $id): Response
    {
        $chapitre = $this->chapitreRepository->find($id);

        if (!$chapitre || $chapitre->getCours()->getStatus() !== 'APPROVED') {
            throw $this->createNotFoundException('Chapter not found or course not approved');
        }

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

    #[Route('/{id}/update', name: 'update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $chapitre = $this->chapitreRepository->find($id);

        if (!$chapitre || $chapitre->getCours()->getStatus() !== 'APPROVED') {
            return new JsonResponse(['success' => false, 'message' => 'Chapter not found or not editable'], 404);
        }

        // Read plain form-encoded content (no JSON decoding)
        $contentHtml = $request->request->get('content', '');
        if ($contentHtml !== '') {
            $chapitre->setContent($contentHtml);
        }

        $violations = $this->validator->validate($chapitre);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[] = [
                    'field' => $v->getPropertyPath(),
                    'message' => $v->getMessage(),
                ];
            }
            return new JsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }
}
