<?php

namespace App\Controller\Student;

use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/cours', name: 'student_cours_')]
class StudentCoursController extends AbstractController
{
    public function __construct(private CoursRepository $coursRepository) {}

    // Dashboard: Only approved courses (read-only)
    #[Route('', name: 'index')]
    public function index(): Response
    {
        $approvedCourses = $this->coursRepository->findApproved();

        $instructors = [];
        $categories = [];
        $totalChapters = 0;
        foreach ($approvedCourses as $c) {
            $instructors[] = $c->getCreatedBy();
            $categories[] = $c->getCategory();
            $totalChapters += count($c->getChapitres());
        }

        $uniqueInstructorsCount = count(array_unique(array_filter($instructors)));
        $uniqueCategoriesCount = count(array_unique(array_filter($categories)));

        return $this->render('student/cours/index.html.twig', [
            'courses' => $approvedCourses,
            'uniqueInstructorsCount' => $uniqueInstructorsCount,
            'uniqueCategoriesCount' => $uniqueCategoriesCount,
            'totalChaptersCount' => $totalChapters,
        ]);
    }

    // View details of a course (read-only)
    #[Route('/{id}', name: 'show')]
    public function show(int $id): Response
    {
        $cours = $this->coursRepository->find($id);

        if (!$cours || $cours->getStatus() !== 'APPROVED') {
            throw $this->createNotFoundException('Course not found or not approved');
        }

        return $this->render('student/cours/show.html.twig', [
            'cours' => $cours,
        ]);
    }
}
