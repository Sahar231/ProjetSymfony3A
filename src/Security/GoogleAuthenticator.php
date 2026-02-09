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

                // 1) Have they logged in with Google before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2) Have they signed up with this email before?
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                // 3) Create a new user if not
                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setFullName($googleUser->getName());
                    $user->setGoogleId($googleUser->getId());
                    $user->setCreatedAt(new \DateTimeImmutable());
                    
                    // Retrieve chosen role from session
                    $session = $request->getSession();
                    $chosenRole = $session->get('oauth_role', 'student');
                    $session->remove('oauth_role'); // Clean up

                    if ($chosenRole === 'instructor') {
                        $user->setRoles(['ROLE_INSTRUCTOR']);
                        $user->setRole('Instructor');
                    } else {
                        $user->setRoles(['ROLE_STUDENT']);
                        $user->setRole('Student');
                    }

                    $user->setIsApproved(false); // Make them pending so Admin sees the request
                } else {
                    // Update existing user with Google ID
                    $user->setGoogleId($googleUser->getId());
                }

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

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        if (in_array('ROLE_INSTRUCTOR', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('instructor_manage_courses'));
        }

        if (in_array('ROLE_STUDENT', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('student_courses'));
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        $request->getSession()->getFlashBag()->add('danger', $message);

        return new RedirectResponse($this->router->generate('app_login'));
    }

   /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the "login".
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/login', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
