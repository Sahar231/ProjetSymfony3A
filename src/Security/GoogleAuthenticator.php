<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client, $request) {
                /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // Retrieve chosen role from session (set in GoogleController) or cookie fallback
                $session = $request->getSession();
                $chosenRole = $session->get('oauth_role') ?: $request->cookies->get('oauth_role'); 
                
                $expectedSymfonyRole = $chosenRole === 'instructor' ? 'ROLE_INSTRUCTOR' : ($chosenRole === 'student' ? 'ROLE_STUDENT' : null);

                // 1) Have they logged in with Google before?
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    if ($expectedSymfonyRole && !in_array($expectedSymfonyRole, $existingUser->getRoles(), true)) {
                        throw new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException(
                            'Cette adresse e-mail est déjà associée à un autre compte.'
                        );
                    }
                    $session->remove('oauth_role');
                    return $existingUser;
                }

                // 2) Have they signed up with this email before (manual registration)?
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($user) {
                    // Link to Google Account instead of throwing an error
                    if (!$user->getGoogleId()) {
                        $user->setGoogleId($googleUser->getId());
                    }

                    // Role check for existing Google users
                    if ($expectedSymfonyRole && !in_array($expectedSymfonyRole, $user->getRoles(), true)) {
                        throw new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException(
                            'Cette adresse e-mail est déjà associée à un autre compte.'
                        );
                    }
                } else {
                    // 4) Create a new user
                    $user = new User();
                    $user->setEmail($email);
                    $user->setFullName($googleUser->getName());
                    $user->setGoogleId($googleUser->getId());
                    $user->setCreatedAt(new \DateTimeImmutable());

                    if ($chosenRole === 'instructor') {
                        $user->setRoles(['ROLE_INSTRUCTOR']);
                        $user->setRole('Instructor');
                    } else {
                        // Default to student if not specified
                        $user->setRoles(['ROLE_STUDENT']);
                        $user->setRole('Student');
                    }

                    $user->setIsApproved(false); // Make them pending
                    $session->set('is_new_google_user', true);
                }

                $session->remove('oauth_role');
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var \App\Entity\User $user */
        $user = $token->getUser();

        if ($request->getSession()->get('is_new_google_user')) {
            $request->getSession()->remove('is_new_google_user');
            $request->getSession()->getFlashBag()->add(
                'success',
                'Inscription réussie. Votre compte est en attente d\'approbation.'
            );
            $response = new RedirectResponse($this->router->generate('app_login'));
            $response->headers->clearCookie('oauth_role');
            return $response;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $response = new RedirectResponse($this->router->generate('admin_dashboard'));
        } elseif (in_array('ROLE_INSTRUCTOR', $user->getRoles(), true)) {
            $response = new RedirectResponse($this->router->generate('instructor_dashboard'));
        } elseif (in_array('ROLE_STUDENT', $user->getRoles(), true)) {
            $response = new RedirectResponse($this->router->generate('student_dashboard'));
        } else {
            $response = new RedirectResponse($this->router->generate('app_home'));
        }
        
        $response->headers->clearCookie('oauth_role');
        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', $exception->getMessageKey());

        $response = new RedirectResponse($this->router->generate('app_login'));
        $response->headers->clearCookie('oauth_role');
        return $response;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse('/login', Response::HTTP_TEMPORARY_REDIRECT);
    }
}
