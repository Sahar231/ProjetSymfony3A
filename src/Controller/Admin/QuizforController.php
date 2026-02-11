<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/quiz')]
class QuizforController extends AbstractController
{
    #[Route('', name: 'admin_quiz_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('admin/quiz/list.html.twig');
    }

    #[Route('/add', name: 'admin_quiz_add', methods: ['GET', 'POST'])]
    public function add(): Response
    {
        return $this->render('admin/quiz/add.html.twig');
    }
}
