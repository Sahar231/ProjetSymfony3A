<?php

namespace App\Controller\Student;

use App\Entity\Formation;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/formations')]
#[IsGranted('ROLE_STUDENT')]
class FormationController extends AbstractController
{
    #[Route('', name: 'student_formations', methods: ['GET'])]
    public function list(FormationRepository $formationRepository): Response
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

    #[Route('/{id}', name: 'student_formation_view', methods: ['GET'])]
    public function view(Formation $formation): Response
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

    #[Route('/{id}/enroll', name: 'student_formation_enroll', methods: ['POST'])]
    public function enroll(Formation $formation, EntityManagerInterface $entityManager): Response
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

    #[Route('/{id}/unenroll', name: 'student_formation_unenroll', methods: ['POST'])]
    public function unenroll(Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->getFormations()->contains($formation)) {
            $user->removeFormation($formation);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully unenrolled from ' . $formation->getTitle());
        }

        return $this->redirectToRoute('student_formations');
    }
}
