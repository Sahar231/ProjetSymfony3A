<?php

namespace App\Controller\Student;

use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/certificates', name: 'student_certificate_')]
#[IsGranted('ROLE_STUDENT')]
class CertificateController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CertificateRepository $certificateRepository): Response
    {
        $user = $this->getUser();
        $certificates = $certificateRepository->findByUser($user);

        return $this->render('student/certificate/index.html.twig', [
            'certificates' => $certificates,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Certificate $certificate): Response
    {
        // Verify the certificate belongs to the current user
        if ($certificate->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this certificate.');
        }

        return $this->render('student/certificate/show.html.twig', [
            'certificate' => $certificate,
        ]);
    }

    #[Route('/{id}/download', name: 'download', methods: ['GET'])]
    public function download(Certificate $certificate): Response
    {
        // Verify the certificate belongs to the current user
        if ($certificate->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this certificate.');
        }

        // In a real app, this would generate a PDF certificate
        // For now, we'll just show the view as a downloadable page
        return $this->render('student/certificate/pdf.html.twig', [
            'certificate' => $certificate,
        ]);
    }
}
