<?php

namespace App\Controller\Instructor;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/instructor/clubs')]
#[IsGranted('ROLE_INSTRUCTOR')]
class ClubController extends AbstractController
{
    #[Route('', name: 'instructor_club_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('instructor/club/list.html.twig');
    }

    #[Route('/create', name: 'instructor_club_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('instructor/club/add.html.twig');
    }
}
