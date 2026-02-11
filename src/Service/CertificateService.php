<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Formation;
use App\Entity\Quiz;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CertificateService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Award a certificate to a student for completing a quiz/formation
     */
    public function awardCertificate(
        User $user,
        Formation $formation,
        float $score,
        ?Quiz $quiz = null
    ): Certificate {
        $certificate = new Certificate();
        $certificate->setUser($user);
        $certificate->setFormation($formation);
        $certificate->setScore($score);
        if ($quiz) {
            $certificate->setQuiz($quiz);
        }

        $this->entityManager->persist($certificate);
        $this->entityManager->flush();

        return $certificate;
    }

    /**
     * Check if a student already has a certificate for a formation
     */
    public function hasCertificate(User $user, Formation $formation): bool
    {
        $certificate = $this->entityManager->getRepository(Certificate::class)
            ->findByUserAndFormation($user, $formation);

        return $certificate !== null;
    }

    /**
     * Get a certificate for a user and formation
     */
    public function getCertificate(User $user, Formation $formation): ?Certificate
    {
        return $this->entityManager->getRepository(Certificate::class)
            ->findByUserAndFormation($user, $formation);
    }

    /**
     * Award certificate if quiz is passed (score >= pass score)
     */
    public function awardCertificateIfPassed(
        User $user,
        Formation $formation,
        Quiz $quiz,
        float $score
    ): ?Certificate {
        // Check if score is passing
        $passScore = (float) $quiz->getPassScore();
        
        if ($score >= $passScore) {
            // Check if certificate already exists
            if (!$this->hasCertificate($user, $formation)) {
                return $this->awardCertificate($user, $formation, $score, $quiz);
            }
        }

        return null;
    }
}
