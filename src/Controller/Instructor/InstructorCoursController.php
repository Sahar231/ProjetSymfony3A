<?php

namespace App\Controller\Instructor;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instructor/cours', name: 'instructor_cours_')]
class InstructorCoursController extends AbstractController
{
    private ValidatorInterface $validator;

    public function __construct(
        private EntityManagerInterface $em,
        private CoursRepository $coursRepository,
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }

    // Dashboard: Own courses + approved courses from others
    #[Route('', name: 'index')]
    public function index(): Response
    {
        // For now, using 'instructor_user1' as placeholder. Will be replaced with actual user
        $instructorName = 'instructor_user1';

        $ownCourses = $this->coursRepository->findInstructorOwnCourses($instructorName);
        $approvedCourses = $this->coursRepository->findApproved();

        // Filter out own courses from approved to avoid duplication
        $otherApprovedCourses = array_filter(
            $approvedCourses,
            fn(Cours $c) => $c->getCreatedBy() !== $instructorName
        );

        return $this->render('instructor/cours/index.html.twig', [
            'ownCourses' => $ownCourses,
            'otherApprovedCourses' => $otherApprovedCourses,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $cours = new Cours();
        $instructorName = 'instructor_user1'; // Placeholder

        $errors = null;
        if ($request->isMethod('POST')) {
            $cours->setTitle($request->request->get('title', ''));
            $cours->setDescription($request->request->get('description', ''));
            $cours->setCategory($request->request->get('category', ''));
            $cours->setStatus('PENDING'); // Always PENDING for instructor
            $cours->setCreatedBy($instructorName);

            $errorsList = $this->validator->validate($cours);
            if (count($errorsList) > 0) {
                $errors = $errorsList;
            } else {
                $this->em->persist($cours);
                $this->em->flush();

                $this->addFlash('success', 'Cours créé avec succès. En attente d\'approbation.');

                return $this->redirectToRoute('instructor_cours_index');
            }
        }

        return $this->render('instructor/cours/form.html.twig', [
            'cours' => $cours,
            'isEdit' => false,
            'errors' => $errors,
        ]);
    }

    // Edit own course only
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Cours $cours, Request $request): Response
    {
        $instructorName = 'instructor_user1';

        // Authorization check
        if ($cours->getCreatedBy() !== $instructorName) {
            throw $this->createAccessDeniedException('You can only edit your own courses');
        }

        $errors = null;
        if ($request->isMethod('POST')) {
            $cours->setTitle($request->request->get('title', ''));
            $cours->setDescription($request->request->get('description', ''));
            $cours->setCategory($request->request->get('category', ''));

            $errorsList = $this->validator->validate($cours);
            if (count($errorsList) > 0) {
                $errors = $errorsList;
            } else {
                $this->em->flush();
                $this->addFlash('success', 'Cours mis à jour avec succès');

                return $this->redirectToRoute('instructor_cours_index');
            }
        }

        return $this->render('instructor/cours/form.html.twig', [
            'cours' => $cours,
            'isEdit' => true,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Cours $cours): Response
    {
        return $this->render('instructor/cours/show.html.twig', [
            'cours' => $cours,
        ]);
    }

    // Delete own course only
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Cours $cours, Request $request): Response
    {
        $instructorName = 'instructor_user1';

        if ($cours->getCreatedBy() !== $instructorName) {
            throw $this->createAccessDeniedException('You can only delete your own courses');
        }

        if ($this->isCsrfTokenValid('delete' . $cours->getId(), $request->request->get('_token'))) {
            $this->em->remove($cours);
            $this->em->flush();
            $this->addFlash('success', 'Cours supprimé avec succès');
        }

        return $this->redirectToRoute('instructor_cours_index');
    }
}
