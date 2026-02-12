<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Club;
use App\Entity\Event;
use App\Form\ProfileFormType;
use App\Repository\FormationRepository;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/student/dashboard', name: 'student_dashboard')]
    #[IsGranted('ROLE_STUDENT')]
    public function studentDashboard(FormationRepository $formationRepository, ClubRepository $clubRepository): Response
    {
        $user = $this->getUser();

        // Get clubs the user is a member of (approved clubs only)
        $myClubs = $clubRepository->createQueryBuilder('c')
            ->join('c.members', 'm')
            ->where('m.id = :userId')
            ->andWhere('c.status = :status')
            ->setParameter('userId', $user->getId())
            ->setParameter('status', Club::STATUS_APPROVED)
            ->getQuery()
            ->getResult();

        return $this->render('student/student-dashboard.html.twig', [
            'enrolledFormations' => $user->getFormations(),
            'availableFormations' => $formationRepository->findBy(['isApproved' => true]),
            'enrollmentCount' => count($user->getFormations()),
            'myClubs' => $myClubs,
        ]);
    }

    #[Route('/instructor/dashboard', name: 'instructor_dashboard')]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function instructorDashboard(ClubRepository $clubRepository, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();

        // Get clubs created by this instructor
        $instructorClubs = $clubRepository->createQueryBuilder('c')
            ->where('c.creator = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        // Get events created by this instructor
        $instructorEvents = $eventRepository->createQueryBuilder('e')
            ->where('e.creator = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        return $this->render('instructor/instructor-dashboard.html.twig', [
            'instructorClubs' => $instructorClubs,
            'instructorEvents' => $instructorEvents,
        ]);
    }

    #[Route('/student/profile/edit', name: 'student_profile_edit')]
    #[IsGranted('ROLE_STUDENT')]
    public function studentProfileEdit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('student_profile_edit');
        }

        return $this->render('student/student-edit-profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/instructor/profile/edit', name: 'instructor_profile_edit')]
    #[IsGranted('ROLE_INSTRUCTOR')]
    public function instructorProfileEdit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('instructor_profile_edit');
        }

        return $this->render('instructor/instructor-edit-profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
}
