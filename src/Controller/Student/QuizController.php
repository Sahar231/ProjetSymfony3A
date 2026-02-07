<?php

namespace App\Controller\Student;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/quiz')]
class QuizController extends AbstractController
{
    #[Route('', name: 'student_quiz_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('student/quiz/list.html.twig');
    }

    #[Route('/start', name: 'student_quiz_start', methods: ['GET', 'POST'])]
    public function start(): Response
    {
        return $this->render('student/quiz/add.html.twig');
    }
}
