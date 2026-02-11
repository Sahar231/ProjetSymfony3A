<?php

namespace App\Repository;

use App\Entity\Certificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificate>
 *
 * @method Certificate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Certificate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Certificate[]    findAll()
 * @method Certificate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificate::class);
    }

    public function findByUser($user)
    {
        return $this->findBy(['user' => $user], ['awardedAt' => 'DESC']);
    }

    public function findByUserAndFormation($user, $formation)
    {
        return $this->findOneBy(['user' => $user, 'formation' => $formation]);
    }

    public function findByUserAndQuiz($user, $quiz)
    {
        return $this->findOneBy(['user' => $user, 'quiz' => $quiz]);
    }
}
