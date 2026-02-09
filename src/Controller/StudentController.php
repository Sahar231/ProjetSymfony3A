<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\FormationType;

#[Route('/student', name: 'student_')]
#[IsGranted('ROLE_STUDENT')]
class StudentController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(FormationRepository $formationRepository): Response
    {
        $user = $this->getUser();

        // Filter enrolled formations to exclude archived ones
        $enrolledFormations = $user->getFormations()->filter(function (Formation $formation) {
            return !$formation->isArchived();
        });

        return $this->render('student/student-dashboard.html.twig', [
            'enrolledFormations' => $enrolledFormations,
            'availableFormations' => $formationRepository->findApprovedAndNotArchived(),
            'enrollmentCount' => count($enrolledFormations),
        ]);
    }

    #[Route('/formations', name: 'formations')]
    public function formations(FormationRepository $formationRepository): Response
    {
        $user = $this->getUser();

        // Filter enrolled formations to exclude archived ones
        $enrolledFormations = $user->getFormations()->filter(function (Formation $formation) {
            return !$formation->isArchived();
        });

        return $this->render('student/formation/list.html.twig', [
            'enrolledFormations' => $enrolledFormations,
            'availableFormations' => $formationRepository->findApprovedAndNotArchived(),
        ]);
    }

    #[Route('/formation/{id}', name: 'formation_view')]
    public function viewFormation(Formation $formation): Response
    {
        // Prevent viewing archived formations
        if ($formation->isArchived()) {
            $this->addFlash('error', 'This formation has been archived and is no longer available.');
            return $this->redirectToRoute('student_formations');
        }

        return $this->render('student/formation/view.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/formation/{id}/enroll', name: 'formation_enroll', methods: ['POST'])]
    public function enrollFormation(Formation $formation, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user->getFormations()->contains($formation)) {
            $user->addFormation($formation);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully enrolled in ' . $formation->getTitle());
        } else {
            $this->addFlash('warning', 'You are already enrolled in this formation');
        }

        return $this->redirectToRoute('student_formation_view', ['id' => $formation->getId()]);
    }

    #[Route('/formation/{id}/unenroll', name: 'formation_unenroll', methods: ['POST'])]
    public function unenrollFormation(Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->getFormations()->contains($formation)) {
            $user->removeFormation($formation);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully unenrolled from ' . $formation->getTitle());
        }

        return $this->redirectToRoute('student_formations');
    }

    #[Route('/courses', name: 'courses')]
    public function courses(): Response
    {
        return $this->render('student/student-course-list.html.twig');
    }

    #[Route('/course-resume/{id<\d+>}', name: 'course_resume')]
    public function courseResume(int $id): Response
    {
        return $this->render('student/student-course-resume.html.twig', [
            'courseId' => $id,
        ]);
    }

    #[Route('/quiz', name: 'quiz')]
    public function quiz(): Response
    {
        return $this->render('student/student-quiz.html.twig');
    }

    #[Route('/bookmarks', name: 'bookmarks')]
    public function bookmarks(): Response
    {
        return $this->render('student/student-bookmark.html.twig');
    }

    #[Route('/subscription', name: 'subscription')]
    public function subscription(): Response
    {
        return $this->render('student/student-subscription.html.twig');
    }

    #[Route('/payment-info', name: 'payment_info')]
    public function paymentInfo(): Response
    {
        return $this->render('student/student-payment-info.html.twig');
    }
}
