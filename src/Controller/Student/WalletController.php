<?php

namespace App\Controller\Student;

use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/wallet')]
#[IsGranted('ROLE_STUDENT')]
class WalletController extends AbstractController
{
    #[Route('', name: 'student_wallet_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $wallet = $entityManager->getRepository(Wallet::class)->findByUser($user);

        return $this->render('student/wallet/index.html.twig', [
            'wallet' => $wallet,
        ]);
    }

    #[Route('/create', name: 'student_wallet_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Check if user already has a wallet
        $existingWallet = $entityManager->getRepository(Wallet::class)->findByUser($user);
        if ($existingWallet) {
            $this->addFlash('warning', 'You already have a wallet');
            return $this->redirectToRoute('student_wallet_index');
        }

        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');

            // Validate form data
            $errors = [];
            if (empty($firstName)) {
                $errors[] = 'First name is required.';
            }
            if (empty($lastName)) {
                $errors[] = 'Last name is required.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('student_wallet_create');
            }

            // Create new wallet with 100 credits for first-time creation
            $wallet = new Wallet();
            $wallet->setUser($user);
            $wallet->setFirstName($firstName);
            $wallet->setLastName($lastName);
            $wallet->setBalance(100); // First-time bonus of 100 credits

            $entityManager->persist($wallet);
            $entityManager->flush();

            $this->addFlash('success', 'Wallet created successfully! You have received 100 free credits.');
            return $this->redirectToRoute('student_wallet_index');
        }

        return $this->render('student/wallet/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'student_wallet_edit', methods: ['GET', 'POST'])]
    public function edit(Wallet $wallet, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Ensure the wallet belongs to the current user
        if ($wallet->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You can only edit your own wallet');
            return $this->redirectToRoute('student_wallet_index');
        }

        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');

            // Validate form data
            $errors = [];
            if (empty($firstName)) {
                $errors[] = 'First name is required.';
            }
            if (empty($lastName)) {
                $errors[] = 'Last name is required.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('student_wallet_edit', ['id' => $wallet->getId()]);
            }

            $wallet->setFirstName($firstName);
            $wallet->setLastName($lastName);
            $entityManager->flush();

            $this->addFlash('success', 'Wallet information updated successfully!');
            return $this->redirectToRoute('student_wallet_index');
        }

        return $this->render('student/wallet/edit.html.twig', [
            'wallet' => $wallet,
        ]);
    }

    #[Route('/{id}/delete', name: 'student_wallet_delete', methods: ['POST'])]
    public function delete(Wallet $wallet, EntityManagerInterface $entityManager): Response
    {
        // Ensure the wallet belongs to the current user
        if ($wallet->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You can only delete your own wallet');
            return $this->redirectToRoute('student_wallet_index');
        }

        $entityManager->remove($wallet);
        $entityManager->flush();

        $this->addFlash('success', 'Wallet deleted successfully!');
        return $this->redirectToRoute('student_wallet_index');
    }
}
