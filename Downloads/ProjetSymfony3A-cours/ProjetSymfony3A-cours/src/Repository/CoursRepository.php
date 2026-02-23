<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 *
 * @method Cours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cours[]    findAll()
 * @method Cours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    public function findApproved(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', 'APPROVED')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByInstructor(string $instructor): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.createdBy = :instructor')
            ->setParameter('instructor', $instructor)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findInstructorOwnCourses(string $instructor): array
    {
        return $this->findByInstructor($instructor);
    }

    public function findInstructorVisibleCourses(string $instructor): array
    {
        return $this->createQueryBuilder('c')
            ->where('(c.createdBy = :instructor) OR (c.status = :status)')
            ->setParameter('instructor', $instructor)
            ->setParameter('status', 'APPROVED')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findNotApproved(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status != :status')
            ->setParameter('status', 'APPROVED')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', $status)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
