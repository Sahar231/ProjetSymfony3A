<?php

namespace App\Controller\Instructor;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/events')]
#[IsGranted('ROLE_INSTRUCTOR')]
class EventController extends AbstractController
{
    #[Route('', name: 'instructor_event_list', methods: ['GET'])]
    public function list(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findBy(['creator' => $user]);

        return $this->render('instructor/event/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/create', name: 'instructor_event_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('instructor/event/add.html.twig');
    }

    #[Route('/{id}', name: 'instructor_event_show', methods: ['GET'])]
    public function show(int $id, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $event = $eventRepository->findOneBy(['id' => $id, 'creator' => $user]);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('instructor/event/show.html.twig', [
            'event' => $event,
        ]);
    }
}
