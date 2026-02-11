<?php

namespace App\Controller\Student;

use App\Entity\Quiz;
use App\Service\CertificateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/quiz')]
#[IsGranted('ROLE_STUDENT')]
class QuizController extends AbstractController
{
    public function __construct(
        private CertificateService $certificateService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'student_quiz_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('student/quiz/list.html.twig');
    }

    #[Route('/{id}/start', name: 'student_quiz_start', methods: ['GET'])]
    public function start(Quiz $quiz): Response
    {
        return $this->render('student/quiz/start.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/submit', name: 'student_quiz_submit', methods: ['POST'])]
    public function submit(Quiz $quiz, Request $request): Response
    {
        $user = $this->getUser();
        $formation = $quiz->getFormation();

        // Verify user is enrolled in the formation
        if (!$user->getFormations()->contains($formation)) {
            $this->addFlash('error', 'You must be enrolled in the formation to take this quiz.');
            return $this->redirectToRoute('student_formations');
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
    private function calculateQuizScore(Quiz $quiz, Request $request): float
    {
        $answers = $request->request->all();
        $awardedPoints = 0;
        $questions = $quiz->getQuestions();

        if (count($questions) === 0) {
            return 0;
        }

        // Calculate max points from questions (in case total_score wasn't set)
        $maxPoints = 0;
        foreach ($questions as $question) {
            $maxPoints += (float) $question->getScore();
        }

        // If still no points, fall back to quiz total_score
        if ($maxPoints <= 0) {
            $maxPoints = (float) $quiz->getTotalScore();
        }

        // If still zero, can't calculate
        if ($maxPoints <= 0) {
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
