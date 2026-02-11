<?php

namespace App\Controller\Admin;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/cours', name: 'admin_cours_')]
class AdminCoursController extends AbstractController
{
    private ValidatorInterface $validator;

    public function __construct(
        private EntityManagerInterface $em,
        private CoursRepository $coursRepository,
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $all_cours = $this->coursRepository->findAll();

        return $this->render('admin/cours/index.html.twig', [
            'all_cours' => $all_cours,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $cours = new Cours();

        $errors = null;
        if ($request->isMethod('POST')) {
            $cours->setTitle($request->request->get('title', ''));
            $cours->setDescription($request->request->get('description', ''));
            $cours->setCategory($request->request->get('category', ''));
            $cours->setStatus($request->request->get('status', 'PENDING'));
            $cours->setCreatedBy('admin');

            $violations = $this->validator->validate($cours);
            if (count($violations) > 0) {
                $errors = $violations;
            } else {
                $this->em->persist($cours);
                $this->em->flush();

                $this->addFlash('success', 'Cours créé avec succès');

                return $this->redirectToRoute('admin_cours_index');
            }
        }

        return $this->render('admin/cours/form.html.twig', [
            'cours' => $cours,
            'isEdit' => false,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Cours $cours, Request $request): Response
    {
        $errors = null;
        if ($request->isMethod('POST')) {
            $cours->setTitle($request->request->get('title', ''));
            $cours->setDescription($request->request->get('description', ''));
            $cours->setCategory($request->request->get('category', ''));
            $cours->setStatus($request->request->get('status', 'PENDING'));

            $violations = $this->validator->validate($cours);
            if (count($violations) > 0) {
                $errors = $violations;
            } else {
                $this->em->flush();
                $this->addFlash('success', 'Cours mis à jour avec succès');

                return $this->redirectToRoute('admin_cours_index');
            }
        }

        return $this->render('admin/cours/form.html.twig', [
            'cours' => $cours,
            'isEdit' => true,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Cours $cours): Response
    {
        return $this->render('admin/cours/show.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Cours $cours, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cours->getId(), $request->request->get('_token'))) {
            $this->em->remove($cours);
            $this->em->flush();
            $this->addFlash('success', 'Cours supprimé avec succès');
        }

        return $this->redirectToRoute('admin_cours_index');
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Cours $cours, Request $request): Response
    {
        if ($this->isCsrfTokenValid('approve' . $cours->getId(), $request->request->get('_token'))) {
            $cours->setStatus('APPROVED');
            $cours->setApprovedBy('admin');
            $cours->setApprovedAt(new \DateTime());

            $this->em->flush();
            $this->addFlash('success', 'Cours approuvé');
        }

        return $this->redirectToRoute('admin_cours_index');
    }

    #[Route('/{id}/refuse', name: 'refuse', methods: ['POST'])]
    public function refuse(Cours $cours, Request $request): Response
    {
        if ($this->isCsrfTokenValid('refuse' . $cours->getId(), $request->request->get('_token'))) {
            $cours->setStatus('REFUSED');
            $cours->setApprovedBy('admin');
            $cours->setApprovedAt(new \DateTime());

            $this->em->flush();
            $this->addFlash('warning', 'Cours refusé');
        }

        return $this->redirectToRoute('admin_cours_index');
    }
}
