<?php

namespace App\Controller\Instructor;

use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/formations')]
#[IsGranted('ROLE_INSTRUCTOR')]
class FormationController extends AbstractController
{
    #[Route('', name: 'instructor_formation_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $formations = $entityManager->getRepository(Formation::class)->findAll();

        return $this->render('instructor/formation/list.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/create', name: 'instructor_formation_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $formation = new Formation();
            $formation->setTitle($request->request->get('title'));
            $formation->setDescription($request->request->get('description'));
            $formation->setContent($request->request->get('description'));
            $formation->setPrice(0);

            // Handle file upload
            $uploadedFile = $request->files->get('supportFile');
            if ($uploadedFile) {
                $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/formations';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                try {
                    $uploadedFile->move($uploadDir, $fileName);
                    $formation->setSupportFile('/uploads/formations/' . $fileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                }
            }

            // Associate current instructor with formation
            $user = $this->getUser();
            if ($user) {
                $formation->addUser($user);
            }

            $entityManager->persist($formation);
            $entityManager->flush();

            $this->addFlash('success', 'Formation created successfully! It will be reviewed by admin for approval.');
            return $this->redirectToRoute('instructor_formation_list');
        }

        return $this->render('instructor/formation/add.html.twig');
    }

    #[Route('/{id}/archive', name: 'instructor_formation_archive', methods: ['POST'])]
    public function archive(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        $formation->setArchived(true);
        $entityManager->flush();

        $this->addFlash('success', 'Formation archived successfully!');
        return $this->redirectToRoute('instructor_formation_list');
    }

    #[Route('/{id}/unarchive', name: 'instructor_formation_unarchive', methods: ['POST'])]
    public function unarchive(int $id, EntityManagerInterface $entityManager): Response
    {
        $formation = $entityManager->getRepository(Formation::class)->find($id);

        if (!$formation) {
            $this->addFlash('error', 'Formation not found');
            return $this->redirectToRoute('instructor_formation_list');
        }

        $formation->setArchived(false);
        $entityManager->flush();

        $this->addFlash('success', 'Formation unarchived successfully!');
        return $this->redirectToRoute('instructor_formation_list');
    }
}
