<?php

namespace App\Controller\Admin;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Form\ChapitreType;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/chapitre', name: 'admin_chapitre_')]
class AdminChapitreController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChapitreRepository $chapitreRepository
    ) {}

    #[Route('/cours/{coursId}', name: 'index', methods: ['GET'])]
    public function index(int $coursId): Response
    {
        $cours = $this->em->getRepository(Cours::class)->find($coursId);
        if (!$cours) {
            throw $this->createNotFoundException('Cours not found');
        }

        $chapitres = $this->chapitreRepository->findByCours($coursId);

        return $this->render('admin/chapitre/index.html.twig', [
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

        $chapitre = new Chapitre();
        $chapitre->setCours($cours);

        $form = $this->createForm(ChapitreType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle content JSON conversion
            $content = $request->request->get('content');
            if ($content) {
                $chapitre->setContent(json_decode($content, true) ?? []);
            }

            $this->em->persist($chapitre);
            $this->em->flush();

            $this->addFlash('success', 'Chapitre créé avec succès');

            return $this->redirectToRoute('admin_chapitre_index', ['coursId' => $coursId]);
        }

        return $this->render('admin/chapitre/form.html.twig', [
            'form' => $form,
            'chapitre' => $chapitre,
            'cours' => $cours,
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Chapitre $chapitre, Request $request): Response
    {
        $cours = $chapitre->getCours();

        $form = $this->createForm(ChapitreType::class, $chapitre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle content JSON conversion
            $content = $request->request->get('content');
            if ($content) {
                $chapitre->setContent(json_decode($content, true) ?? []);
            }

            $this->em->flush();
            $this->addFlash('success', 'Chapitre mis à jour avec succès');

            return $this->redirectToRoute('admin_chapitre_index', ['coursId' => $cours->getId()]);
        }

        return $this->render('admin/chapitre/form.html.twig', [
            'form' => $form,
            'chapitre' => $chapitre,
            'cours' => $cours,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Chapitre $chapitre): Response
    {
        return $this->render('admin/chapitre/show.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Chapitre $chapitre, Request $request): Response
    {
        $coursId = $chapitre->getCours()->getId();

        if ($this->isCsrfTokenValid('delete' . $chapitre->getId(), $request->request->get('_token'))) {
            $this->em->remove($chapitre);
            $this->em->flush();
            $this->addFlash('success', 'Chapitre supprimé avec succès');
        }

        return $this->redirectToRoute('admin_chapitre_index', ['coursId' => $coursId]);
    }
}
