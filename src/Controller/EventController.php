<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events', name: 'event_')]
class EventController extends AbstractController
{
    #[Route('/create/{clubId}', name: 'create', requirements: ['clubId' => '\d+'])]
    public function create(int $clubId, Request $request, EntityManagerInterface $entityManager, ClubRepository $clubRepository): Response
    {
        $club = $clubRepository->find($clubId);
        if (!$club) {
            throw $this->createNotFoundException('Club not found');
        }

        $user = $this->getUser();
        if ($club->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException('You are not allowed to add events to this club.');
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setClub($club);
            $event->setStatus('PENDING'); // Default to pending
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('club_detail', ['id' => $club->getId()]);
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
            'club' => $club,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $club = $event->getClub();
        
        if ($club->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException('You are not allowed to edit this event.');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $event->setStatus('PENDING');
            }
            $entityManager->flush();

            return $this->redirectToRoute('club_detail', ['id' => $club->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $club = $event->getClub();

        if ($club->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException('You are not allowed to delete this event.');
        }

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('club_detail', ['id' => $club->getId()]);
    }

    #[Route('/admin/pending', name: 'admin_pending')]
    public function adminPending(EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('event/admin_pending.html.twig', [
            'events' => $eventRepository->findBy(['status' => 'PENDING']),
        ]);
    }

    #[Route('/admin/{id}/approve', name: 'admin_approve', methods: ['POST'])]
    public function approve(Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $event->setStatus('APPROVED');
        $entityManager->flush();

        return $this->redirectToRoute('event_admin_pending');
    }
}
