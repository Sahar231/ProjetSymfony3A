<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 *
 * @method Club|null find($id, $lockMode = null, $lockVersion = null)
 * @method Club|null findOneBy(array $criteria, array $orderBy = null)
 * @method Club[]    findAll()
 * @method Club[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    public function findApprovedClubs(?string $search = null, ?string $sort = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'APPROVED');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($sort === 'name_asc') {
            $qb->orderBy('c.name', 'ASC');
        } elseif ($sort === 'name_desc') {
            $qb->orderBy('c.name', 'DESC');
        } else {
            $qb->orderBy('c.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
    
    public function findPendingClubs()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'PENDING')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
