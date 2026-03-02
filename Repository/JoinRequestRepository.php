<?php

namespace App\Repository;

use App\Entity\JoinRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JoinRequest>
 */
class JoinRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JoinRequest::class);
    }

    public function findPending()
    {
        return $this->createQueryBuilder('jr')
            ->where('jr.status = :status')
            ->setParameter('status', JoinRequest::STATUS_PENDING)
            ->orderBy('jr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('jr')
            ->select('COUNT(jr.id)')
            ->where('jr.status = :status')
            ->setParameter('status', JoinRequest::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByClubAndStatus($club, $status)
    {
        return $this->createQueryBuilder('jr')
            ->where('jr.club = :club')
            ->andWhere('jr.status = :status')
            ->setParameter('club', $club)
            ->setParameter('status', $status)
            ->orderBy('jr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
