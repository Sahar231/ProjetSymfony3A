<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Repository\ClubRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/club', name: 'admin_club_')]
#[IsGranted('ROLE_ADMIN')]
class ClubController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepository,
        private EntityManagerInterface $em,
        private PdfService $pdfService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');
        $status = $request->query->get('status', '');

        $qb = $this->clubRepository->createQueryBuilder('c')
            ->leftJoin('c.creator', 'creator')
            ->addSelect('creator');

        // Filter by status if provided
        if ($status) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        // Apply search filter
        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search OR creator.username LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('c.createdAt', 'ASC');
                break;
            case 'name_asc':
                $qb->orderBy('c.name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('c.name', 'DESC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('c.createdAt', 'DESC');
                break;
        }

        $clubs = $qb->getQuery()->getResult();
        $totalCount = $this->clubRepository->count([]);
        $pendingCount = $this->clubRepository->countPending();
        $approvedCount = $this->clubRepository->countApproved();
        $rejectedCount = $this->clubRepository->countRejected();

        return $this->render('admin/club/list.html.twig', [
            'clubs' => $clubs,
            'search' => $search,
            'sort' => $sort,
            'status' => $status,
            'totalClubs' => $totalCount,
            'pendingClubs' => $pendingCount,
            'approvedClubs' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $club = new Club();
            $club->setName($request->request->get('name'));
            $club->setDescription($request->request->get('description'));
            $club->setCreator($this->getUser());
            $club->setStatus(Club::STATUS_APPROVED);

            $this->em->persist($club);
            $this->em->flush();

            $this->addFlash('success', 'Club created successfully!');
            return $this->redirectToRoute('admin_club_list');
        }

        return $this->render('admin/club/add.html.twig');
    }

    #[Route('/export-list', name: 'export_list', methods: ['GET'])]
    public function exportList(): Response
    {
        $clubs = $this->clubRepository->findAll();
        return $this->pdfService->generateClubsListPdf($clubs);
    }

    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Club $club): Response
    {
        return $this->render('admin/club/show.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Club $club): Response
    {
        if ($request->isMethod('POST')) {
            $club->setName($request->request->get('name'));
            $club->setDescription($request->request->get('description'));
            $club->setUpdatedAt(new \DateTimeImmutable());

            $this->em->flush();
            $this->addFlash('success', 'Club updated successfully!');
            return $this->redirectToRoute('admin_club_show', ['id' => $club->getId()]);
        }

        return $this->render('admin/club/edit.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Club $club): Response
    {
        if ($this->isCsrfTokenValid('delete' . $club->getId(), $request->request->get('_token'))) {
            $this->em->remove($club);
            $this->em->flush();
            $this->addFlash('success', 'Club deleted successfully!');
        }

        return $this->redirectToRoute('admin_club_list');
    }

    #[Route('/{id<\d+>}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, Club $club): Response
    {
        if ($this->isCsrfTokenValid('approve' . $club->getId(), $request->request->get('_token'))) {
            $club->approve();
            $this->em->flush();
            $this->addFlash('success', 'Club approved successfully!');
        }

        return $this->redirectToRoute('admin_club_show', ['id' => $club->getId()]);
    }

    #[Route('/{id<\d+>}/reject', name: 'reject', methods: ['POST'])]
    public function reject(Request $request, Club $club): Response
    {
        if ($this->isCsrfTokenValid('reject' . $club->getId(), $request->request->get('_token'))) {
            $club->reject();
            $this->em->flush();
            $this->addFlash('success', 'Club rejected successfully!');
        }

        return $this->redirectToRoute('admin_club_show', ['id' => $club->getId()]);
    }

    #[Route('/{id<\d+>}/export', name: 'export', methods: ['GET'])]
    public function export(Club $club): Response
    {
        return $this->pdfService->generateClubPdf($club);
    }
}
