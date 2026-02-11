<?php

namespace App\Controller\Student;

use App\Entity\Quizfor;
use App\Repository\CertificateRepository;
use App\Service\CertificateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/quiz')]
#[IsGranted('ROLE_STUDENT')]
class QuizforController extends AbstractController
{
    public function __construct(
        private CertificateService $certificateService,
        private EntityManagerInterface $entityManager,
        private CertificateRepository $certificateRepository,
    ) {
    }

    #[Route('', name: 'student_quiz_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('student/quiz/list.html.twig');
    }

    #[Route('/{id}/start', name: 'student_quiz_start', methods: ['GET'])]
    public function start(Quizfor $quiz): Response
    {
        $user = $this->getUser();
        
        // Check if user has already passed this quiz (has a certificate)
        $certificate = $this->certificateRepository->findByUserAndQuiz($user, $quiz);
        
        if ($certificate) {
            // User has passed this quiz before - redirect to view-only results
            $this->addFlash('info', 'You have already passed this quiz. You can view your results below.');
            return $this->redirectToRoute('student_quiz_results', ['id' => $quiz->getId()]);
        }

        return $this->render('student/quiz/start.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/results', name: 'student_quiz_results', methods: ['GET'])]
    public function viewResults(Quizfor $quiz): Response
    {
        $user = $this->getUser();
        $formation = $quiz->getFormation();

        // Verify user is enrolled in the formation
        if (!$user->getFormations()->contains($formation)) {
            $this->addFlash('error', 'You must be enrolled in the formation to view this quiz.');
            return $this->redirectToRoute('student_formations');
        }

        // Get the certificate for this user and quiz (should exist if they passed)
        $certificate = $this->certificateRepository->findByUserAndQuiz($user, $quiz);

        if (!$certificate) {
            $this->addFlash('error', 'No passed quiz record found. Please take the quiz first.');
            return $this->redirectToRoute('student_formation_view', ['id' => $formation->getId()]);
        }

        // Render view-only results page
        return $this->render('student/quiz/passed-results.html.twig', [
            'quiz' => $quiz,
            'certificate' => $certificate,
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/submit', name: 'student_quiz_submit', methods: ['POST'])]
    public function submit(Quizfor $quiz, Request $request): Response
    {
        $user = $this->getUser();
        $formation = $quiz->getFormation();

        // Verify user is enrolled in the formation
        if (!$user->getFormations()->contains($formation)) {
            $this->addFlash('error', 'You must be enrolled in the formation to take this quiz.');
            return $this->redirectToRoute('student_formations');
        }

        // Check if user has already passed this quiz
        $existingCertificate = $this->certificateRepository->findByUserAndQuiz($user, $quiz);
        if ($existingCertificate) {
            $this->addFlash('warning', 'You have already passed this quiz and cannot retake it.');
            return $this->redirectToRoute('student_quiz_results', ['id' => $quiz->getId()]);
        }

        // Calculate score from submitted answers
        $score = $this->calculateQuizScore($quiz, $request);

        // Get passing score threshold
        $passScore = (float) $quiz->getPassScore();
        $passed = $score >= $passScore;
        $certificate = null;

        if ($passed) {
            // Quiz passed - award certificate
            $certificate = $this->certificateService->awardCertificateIfPassed(
                $user,
                $formation,
                $quiz,
                $score
            );
        }

        // Render results page instead of redirecting
        return $this->render('student/quiz/result.html.twig', [
            'quiz' => $quiz,
            'score' => $score,
            'passScore' => $passScore,
            'passed' => $passed,
            'certificate' => $certificate,
            'formation' => $formation,
        ]);
    }

    /**
     * Calculate quiz score based on submitted answers
     * Awards points per correct question and calculates percentage
     */
    private function calculateQuizScore(Quizfor $quiz, Request $request): float
    {
        $answers = $request->request->all();
        $awardedPoints = 0;
        $maxPoints = (float) $quiz->getTotalScore();
        $questions = $quiz->getQuestions();

        if ($maxPoints <= 0 || count($questions) === 0) {
            return 0;
        }

        // Iterate through all questions and award points for correct answers
        foreach ($questions as $question) {
            $questionId = 'question_' . $question->getId();
            $studentAnswer = $answers[$questionId] ?? '';
            $correctAnswer = strtolower(trim($question->getReply() ?? ''));
            $studentAnswerNormalized = strtolower(trim($studentAnswer));

            // Compare answers (case-insensitive and trimmed)
            if ($studentAnswerNormalized === $correctAnswer) {
                // Award the points for this question
                $awardedPoints += (float) $question->getScore();
            }
        }

        // Calculate percentage score: (awarded_points / max_points) * 100
        $percentageScore = ($awardedPoints / $maxPoints) * 100;

        // Return rounded to 2 decimal places
        return round($percentageScore, 2);
    }
}
