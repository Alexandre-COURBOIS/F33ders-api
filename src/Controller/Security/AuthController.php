<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthController
 * @package App\Controller
 * @Route("/api", name="api_security_auth_")
 */
class AuthController extends AbstractController
{

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param AuthenticationUtils $authenticationUtils
     * @return JsonResponse
     */
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return new JsonResponse([$lastUsername, $error]);
    }

    /**
     * @Route("/login_check", name="login_check", methods={"POST"})
     * @return JsonResponse
     */
    public function loginCheck(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'user' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }


    /**
     * @Route("/logout", name="logout", methods={"POST"})
     */
    public function logout()
    {

    }
}
