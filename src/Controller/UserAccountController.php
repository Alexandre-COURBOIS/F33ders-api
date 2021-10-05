<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAccountController extends AbstractController
{

    private SerializerService $serializer;

    public function __construct(SerializerService $serializerService)
    {
        $this->serializer = $serializerService;
    }

    /**
     * @Route("/account_activation", name="account_activation")
     */
    public function activateAccountOnRegister(Request $request, UserRepository $userRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data) {
            $user = $userRepository->findOneBy(['token' => $data['token']]);
            if ($user) {

                $user->setIsActive(true);
                $user->setToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
                $user->setUpdatedAt(new \DateTime());

                return new JsonResponse("Votre compte a bien été activé, vous pouvez désormais vous connecter.", Response::HTTP_OK);

            } else {
                return new JsonResponse("Merci de renseigner des données valide.", Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse("Aucune donnée renseignées. Renouvelez votre demande.", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/api/get_user", name="api_get_user", methods={"GET"})
     * @return Response
     */
    public function getUserInformations(): Response
    {
        $user = $this->getUser();

        if ($user) {
            return JsonResponse::fromJsonString($this->serializer->SimpleSerializer($user, 'json'));
        } else {
            return new JsonResponse("Aucune informations", Response::HTTP_BAD_REQUEST);
        }
    }
}
