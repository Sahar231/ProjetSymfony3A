<?php

namespace App\Controller\Student;

use App\Entity\Cours;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/chapitre', name: 'student_chapitre_')]
class StudentChapitreController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChapitreRepository $chapitreRepository
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
}
