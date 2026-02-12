<?php

namespace App\Controller\Admin;

use App\Entity\JoinRequest;
use App\Repository\JoinRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/join-request', name: 'admin_join_request_')]
#[IsGranted('ROLE_ADMIN')]
class JoinRequestController extends AbstractController
{
    public function __construct(
        private JoinRequestRepository $joinRequestRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $status = $request->query->get('status', 'PENDING');
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_desc');

        $qb = $this->joinRequestRepository->createQueryBuilder('jr')
            ->leftJoin('jr.club', 'club')
            ->addSelect('club')
            ->leftJoin('jr.user', 'user')
            ->addSelect('user');

        // Filter by status
        if ($status) {
            $qb->andWhere('jr.status = :status')
               ->setParameter('status', $status);
        }

        // Apply search filter
        if ($search) {
            $qb->andWhere('club.name LIKE :search OR user.username LIKE :search')
               ->setParameter('search', "%$search%");
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('jr.requestedAt', 'ASC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('jr.requestedAt', 'DESC');
                break;
        }

        $joinRequests = $qb->getQuery()->getResult();
        $pendingCount = $this->joinRequestRepository->countPending();

        return $this->render('admin/join_request/list.html.twig', [
            'joinRequests' => $joinRequests,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'pendingCount' => $pendingCount,
        ]);
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Request $request, JoinRequest $joinRequest): Response
    {
        if ($this->isCsrfTokenValid('approve' . $joinRequest->getId(), $request->request->get('_token'))) {
            $joinRequest->approve();
            
            // Add user to club members
            $joinRequest->getClub()->addMember($joinRequest->getUser());
            
            $this->em->flush();
            $this->addFlash('success', 'Join request approved successfully!');
        }

        return $this->redirectToRoute('admin_join_request_list');
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(Request $request, JoinRequest $joinRequest): Response
    {
        if ($this->isCsrfTokenValid('reject' . $joinRequest->getId(), $request->request->get('_token'))) {
            $joinRequest->reject();
            $this->em->flush();
            $this->addFlash('success', 'Join request rejected successfully!');
        }

        return $this->redirectToRoute('admin_join_request_list');
    }
}
