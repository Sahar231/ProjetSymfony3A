<?php

namespace App\Controller\Admin;

use App\Entity\Formation;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/dashboard')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $qb = $entityManager->createQueryBuilder();
        
        // Get pending formations with eager loading of creator
        $pendingFormations = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isApproved = :approved')
            ->andWhere('f.isArchived = :archived')
            ->setParameter('approved', false)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();

        // Get all approved formations
        $qb = $entityManager->createQueryBuilder();
        $approvedFormations = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isApproved = :approved')
            ->andWhere('f.isArchived = :archived')
            ->setParameter('approved', true)
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();

        // Get archived formations
        $qb = $entityManager->createQueryBuilder();
        $archivedFormations = $qb
            ->select('f')
            ->from(Formation::class, 'f')
            ->leftJoin('f.creator', 'c')
            ->addSelect('c')
            ->where('f.isArchived = :archived')
            ->setParameter('archived', true)
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard.html.twig', [
            'pendingFormations' => $pendingFormations,
            'approvedFormations' => $approvedFormations,
            'archivedFormations' => $archivedFormations,
            'pendingCount' => count($pendingFormations),
            'approvedCount' => count($approvedFormations),
            'archivedCount' => count($archivedFormations),
        ]);
    }

    #[Route('/approvals/formations', name: 'admin_formations_approval', methods: ['GET'])]
    public function approvalsFormations(EntityManagerInterface $entityManager): Response
    {
        $status = $_GET['status'] ?? 'pending';
        
        $qb = $entityManager->createQueryBuilder();
        
        $formations = match($status) {
            'pending' => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isApproved = :approved')
                ->andWhere('f.isArchived = :archived')
                ->setParameter('approved', false)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult(),
            'approved' => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isApproved = :approved')
                ->andWhere('f.isArchived = :archived')
                ->setParameter('approved', true)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult(),
            'archived' => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isArchived = :archived')
                ->setParameter('archived', true)
                ->getQuery()
                ->getResult(),
            default => $qb
                ->select('f')
                ->from(Formation::class, 'f')
                ->leftJoin('f.creator', 'c')
                ->addSelect('c')
                ->where('f.isApproved = :approved')
                ->andWhere('f.isArchived = :archived')
                ->setParameter('approved', false)
                ->setParameter('archived', false)
                ->getQuery()
                ->getResult()
        };

        return $this->render('admin/approvals_formations.html.twig', [
            'formations' => $formations,
            'status' => $status,
        ]);
    }
}
