<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Club;
use App\Entity\Formation;
use App\Entity\Evaluation;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\ClubRepository;
use App\Repository\FormationRepository;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Service\Paginator;
use App\Service\PdfService;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private Paginator $paginator,
        private PdfService $pdfService
    ) {}

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        CourseRepository $courseRepo,
        ClubRepository $clubRepo,
        FormationRepository $formationRepo,
        EvaluationRepository $evaluationRepo
    ): Response
    {
        $allUsers = $userRepository->findAll();
        
        $stats = [
            'total_students' => count(array_filter($allUsers, fn($u) => in_array('ROLE_STUDENT', $u->getRoles()))),
            'active_instructors' => count(array_filter($allUsers, fn($u) => in_array('ROLE_INSTRUCTOR', $u->getRoles()) && $u->isApproved())),
            'pending_instructors' => count(array_filter($allUsers, fn($u) => in_array('ROLE_INSTRUCTOR', $u->getRoles()) && !$u->isApproved())),
            'pending_students' => count(array_filter($allUsers, fn($u) => in_array('ROLE_STUDENT', $u->getRoles()) && !$u->isApproved())),
            'blocked_users' => count(array_filter($allUsers, fn($u) => $u->isBlocked())),
            'pending_courses' => count($courseRepo->findBy(['isApproved' => false])),
            'pending_clubs' => count($clubRepo->findBy(['isApproved' => false])),
            'pending_formations' => count($formationRepo->findBy(['isApproved' => false])),
            'pending_evaluations' => count($evaluationRepo->findBy(['isApproved' => false])),
        ];

        $stats['total_pending'] = $stats['pending_instructors'] + $stats['pending_students'] + 
                                  $stats['pending_courses'] + $stats['pending_clubs'] + 
                                  $stats['pending_formations'] + $stats['pending_evaluations'];

        return $this->render('admin/admin-dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/students', name: 'students')]
    public function students(Request $request, UserRepository $userRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'asc');
        $page = (int) $request->query->get('page', 1);

        $qb = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_STUDENT"%');

        if ($search) {
            $qb->andWhere('u.fullName LIKE :q OR u.email LIKE :q')
               ->setParameter('q', "%$search%");
        }

        // Mapping sort fields to entity properties
        $allowedSorts = ['id' => 'u.id', 'fullName' => 'u.fullName', 'email' => 'u.email', 'isBlocked' => 'u.isBlocked'];
        $sortField = $allowedSorts[$sort] ?? 'u.id';
        
        $qb->orderBy($sortField, strtoupper($direction));

        $pagination = $this->paginator->paginate($qb, $page, 10);

        return $this->render('admin/admin-student-list.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/students/export', name: 'students_export')]
    public function exportStudents(UserRepository $userRepository): Response
    {
        $students = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_STUDENT"%')
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();

        $html = $this->renderView('admin/pdf/students_pdf.html.twig', [
            'students' => $students
        ]);

        $pdfContent = $this->pdfService->generateBinaryPDF($html);
        
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="students.pdf"',
        ]);
    }

    #[Route('/instructors', name: 'instructors')]
    public function instructors(Request $request, UserRepository $userRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'asc');
        $page = (int) $request->query->get('page', 1);

        $qb = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isApproved = :approved')
            ->setParameter('role', '%"ROLE_INSTRUCTOR"%')
            ->setParameter('approved', true);

        if ($search) {
            $qb->andWhere('u.fullName LIKE :q OR u.email LIKE :q')
               ->setParameter('q', "%$search%");
        }

        $allowedSorts = ['id' => 'u.id', 'fullName' => 'u.fullName', 'email' => 'u.email', 'isBlocked' => 'u.isBlocked'];
        $sortField = $allowedSorts[$sort] ?? 'u.id';

        $qb->orderBy($sortField, strtoupper($direction));

        $pagination = $this->paginator->paginate($qb, $page, 10);

        return $this->render('admin/admin-instructor-list.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/instructors/export', name: 'instructors_export')]
    public function exportInstructors(UserRepository $userRepository): Response
    {
        $instructors = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isApproved = :approved')
            ->setParameter('role', '%"ROLE_INSTRUCTOR"%')
            ->setParameter('approved', true)
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();

        $html = $this->renderView('admin/pdf/instructors_pdf.html.twig', [
            'instructors' => $instructors
        ]);

        $pdfContent = $this->pdfService->generateBinaryPDF($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="instructors.pdf"',
        ]);
    }

    #[Route('/instructor-requests', name: 'instructor_requests')]
    public function instructorRequests(UserRepository $userRepository): Response
    {
        $requests = array_filter($userRepository->findAll(), fn($u) => in_array('ROLE_INSTRUCTOR', $u->getRoles()) && !$u->isApproved());
        return $this->render('admin/admin-instructor-request.html.twig', [
            'requests' => $requests,
            'type' => 'Instructor'
        ]);
    }

    #[Route('/student-requests', name: 'student_requests')]
    public function studentRequests(UserRepository $userRepository): Response
    {
        $requests = array_filter($userRepository->findAll(), fn($u) => in_array('ROLE_STUDENT', $u->getRoles()) && !$u->isApproved());
        return $this->render('admin/admin-instructor-request.html.twig', [
            'requests' => $requests,
            'type' => 'Student'
        ]);
    }

    #[Route('/approvals/{type}', name: 'approvals')]
    public function approvals(string $type, EntityManagerInterface $em): Response
    {
        $items = match($type) {
            'courses' => $em->getRepository(Course::class)->findBy(['isApproved' => false]),
            'clubs' => $em->getRepository(Club::class)->findBy(['isApproved' => false]),
            'formations' => $em->getRepository(Formation::class)->findBy(['isApproved' => false]),
            'evaluations' => $em->getRepository(Evaluation::class)->findBy(['isApproved' => false]),
            default => []
        };

        return $this->render('admin/approvals-list.html.twig', [
            'items' => $items,
            'type' => $type
        ]);
    }

    #[Route('/user/{id}/toggle-block', name: 'user_toggle_block')]
    public function toggleBlock(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsBlocked(!$user->isBlocked());
        $em->flush();
        $this->addFlash('success', 'User status updated successfully.');
        return $this->redirect($this->generateUrl('admin_' . (in_array('ROLE_STUDENT', $user->getRoles()) ? 'students' : 'instructors')));
    }

    #[Route('/user/{id}/approve', name: 'user_approve')]
    public function approve(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsApproved(true);
        $em->flush();
        $this->addFlash('success', 'User approved.');
        return $this->redirectToRoute(in_array('ROLE_STUDENT', $user->getRoles()) ? 'admin_student_requests' : 'admin_instructor_requests');
    }

    #[Route('/user/{id}/reject', name: 'user_reject')]
    public function reject(User $user, EntityManagerInterface $em): Response
    {
        $role = in_array('ROLE_STUDENT', $user->getRoles()) ? 'student' : 'instructor';
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Request rejected and user removed.');
        return $this->redirectToRoute('admin_' . $role . '_requests');
    }

    #[Route('/approve-item/{type}/{id}', name: 'item_approve')]
    public function approveItem(string $type, int $id, EntityManagerInterface $em): Response
    {
        $item = match($type) {
            'courses' => $em->getRepository(Course::class)->find($id),
            'clubs' => $em->getRepository(Club::class)->find($id),
            'formations' => $em->getRepository(Formation::class)->find($id),
            'evaluations' => $em->getRepository(Evaluation::class)->find($id),
            default => null
        };

        if ($item) {
            $item->setIsApproved(true);
            $em->flush();
            $this->addFlash('success', 'Item approved.');
        }

        return $this->redirectToRoute('admin_approvals', ['type' => $type]);
    }

    #[Route('/reject-item/{type}/{id}', name: 'item_reject')]
    public function rejectItem(string $type, int $id, EntityManagerInterface $em): Response
    {
        $item = match($type) {
            'courses' => $em->getRepository(Course::class)->find($id),
            'clubs' => $em->getRepository(Club::class)->find($id),
            'formations' => $em->getRepository(Formation::class)->find($id),
            'evaluations' => $em->getRepository(Evaluation::class)->find($id),
            default => null
        };

        if ($item) {
            $em->remove($item);
            $em->flush();
            $this->addFlash('success', 'Item rejected.');
        }

        return $this->redirectToRoute('admin_approvals', ['type' => $type]);
    }

    #[Route('/user/add/{role}', name: 'user_add')]
    public function addUser(string $role, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $user->setRole(ucfirst($role));
        $form = $this->createForm(\App\Form\UserFormType::class, $user, ['is_creation' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setIsApproved(true);
            $user->setRoles([$role === 'instructor' ? 'ROLE_INSTRUCTOR' : 'ROLE_STUDENT']);
            $user->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute($role === 'instructor' ? 'admin_instructors' : 'admin_students');
        }

        return $this->render('admin/user-form.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
            'action' => 'Add'
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleInput = $user->getRole();
            $user->setRoles([$roleInput === 'Instructor' ? 'ROLE_INSTRUCTOR' : 'ROLE_STUDENT']);
            $em->flush();

            return $this->redirectToRoute($user->getRole() === 'Instructor' ? 'admin_instructors' : 'admin_students');
        }

        return $this->render('admin/user-form.html.twig', [
            'form' => $form->createView(),
            'role' => strtolower($user->getRole()),
            'action' => 'Edit'
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $role = strtolower($user->getRole());
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute($role === 'instructor' ? 'admin_instructors' : 'admin_students');
    }

    #[Route('/courses', name: 'courses')]
    public function courses(): Response
    {
        return $this->render('admin/admin-course-list.html.twig');
    }

    #[Route('/course-category', name: 'course_category')]
    public function courseCategory(): Response
    {
        return $this->render('admin/admin-course-category.html.twig');
    }

    #[Route('/reviews', name: 'reviews')]
    public function reviews(): Response
    {
        return $this->render('admin/admin-review.html.twig');
    }

    #[Route('/earnings', name: 'earnings')]
    public function earnings(): Response
    {
        return $this->render('admin/admin-earning.html.twig');
    }

    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        return $this->render('admin/admin-setting.html.twig');
    }
}
