<?php

namespace App\Controller;

use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clubs', name: 'club_')]
class ClubController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(Request $request, ClubRepository $clubRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        return $this->render('club/club-list.html.twig', [
            'clubs' => $clubRepository->findApprovedClubs($search, $sort),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/pdf', name: 'pdf')]
    public function pdf(ClubRepository $clubRepository): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('club/pdf.html.twig', [
            'clubs' => $clubRepository->findApprovedClubs(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="clubs.pdf"',
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            // Since we don't have login page, maybe redirect to home or show error
            // For now, let's assume user is logged in via some mechanism or we just throw exception
             throw $this->createAccessDeniedException('You must be logged in to create a club.');
        }

        $club = new Club();
        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $club->setCreator($user);
            $club->addMember($user);
            $club->setStatus('PENDING');
            $entityManager->persist($club);
            $entityManager->flush();

            return $this->redirectToRoute('club_my_clubs');
        }

        return $this->render('club/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/my-clubs', name: 'my_clubs')]
    public function myClubs(): Response
    {
        $user = $this->getUser();
        if (!$user) {
             throw $this->createAccessDeniedException('You must be logged in.');
        }

        return $this->render('club/my_clubs.html.twig', [
            'clubs' => $user->getCreatedClubs(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Club $club, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($club->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You are not allowed to edit this club.');
        }

        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Re-submit for approval on edit? Or keep status?
            // User requirement: "etudiant et enseignat : creer club et createur faire update et suprimer ce club tant que admine accept dans ce dashboard"
            // Suggesting they can edit while pending? Or always?
            // Let's assume they can edit anytime but if approved, maybe it goes back to pending?
            // For simplicity, let's keep status as is or make it PENDING if edited by non-admin.
            if (!$this->isGranted('ROLE_ADMIN')) {
                 $club->setStatus('PENDING');
            }
            $entityManager->flush();

            return $this->redirectToRoute('club_my_clubs');
        }

        return $this->render('club/edit.html.twig', [
            'club' => $club,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Club $club, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($club->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException('You are not allowed to delete this club.');
        }

        if ($this->isCsrfTokenValid('delete'.$club->getId(), $request->request->get('_token'))) {
            $entityManager->remove($club);
            $entityManager->flush();
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('club_admin_pending');
        }
        return $this->redirectToRoute('club_my_clubs');
    }

    #[Route('/{id}/join', name: 'join', requirements: ['id' => '\d+'])]
    public function join(Club $club, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        
        $club->addMember($user);
        $entityManager->flush();
        
        return $this->redirectToRoute('club_list');
    }

    #[Route('/admin/pending', name: 'admin_pending')]
    public function adminPending(ClubRepository $clubRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('club/admin_pending.html.twig', [
            'clubs' => $clubRepository->findPendingClubs(),
        ]);
    }

    #[Route('/admin/{id}/approve', name: 'admin_approve', methods: ['POST'])]
    public function approve(Club $club, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $club->setStatus('APPROVED');
        $entityManager->flush();

        return $this->redirectToRoute('club_admin_pending');
    }
    
    #[Route('/detail/{id<\d+>}', name: 'detail')]
    public function detail(Club $club): Response
    {
        return $this->render('club/club-detail.html.twig', [
            'club' => $club,
        ]);
    }
}
