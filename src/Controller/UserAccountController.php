<?php

namespace App\Controller;

use App\Form\UserPasswordUpdateType;
use App\Form\UserUsernameUpdateType;
use App\Repository\UserRepository;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function activateAccountOnRegister(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data) {
            $user = $userRepository->findOneBy(['token' => $data['token']]);
            if ($user) {

                $user->setIsActive(true);
                $user->setToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
                $user->setUpdatedAt(new \DateTime());

                $entityManager->persist($user);
                $entityManager->flush();

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
            return JsonResponse::fromJsonString($this->serializer->SimpleSerializerUser($user, 'json'));
        } else {
            return new JsonResponse("Aucune informations", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/api/get_user_profil", name="api_get_user_profil", methods={"GET"})
     * @return Response
     */
    public function getUserInformationsProfil(): Response
    {
        $user = $this->getUser();

        if ($user) {
            return JsonResponse::fromJsonString($this->serializer->SimpleSerializerUserProfil($user, 'json'));
        } else {
            return new JsonResponse("Aucune informations", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/api/update_password", name="update_user_password", methods={"PATCH"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updatePasswordInformations(Request $request, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {

        //get the data
        $data = json_decode($request->getContent(), true);

        //get users with jwt token storage
        $user = $this->getUser();
        //get the oldpassword in data
        $oldPassword = $data['oldPassword'];

        //Get the both new passwords to compare them
        $newPassword = $data['password'];
        $verifNewpassword = $data['verifPassword'];

        // Compare if the both new password are identical
        if ($newPassword === $verifNewpassword) {
            //compare password in DB with typed oldpassword
            if (password_verify($oldPassword, $user->getPassword())) {

                $form = $this->createForm(UserPasswordUpdateType::class, $user);

                $form->submit($data);

                $validate = $validator->validate($user, null, 'PasswordUpdate');

                if (count($validate) !== 0) {
                    foreach ($validate as $error) {
                        return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                    }
                }

                // Gestion du mot de passe
                $password = $passwordEncoder->encodePassword($user, $user->getPassword());

                $user->setPassword($password);
                $user->setUpdatedAt(new \DateTime());

                $entityManager->persist($user);
                $entityManager->flush();

                return new JsonResponse("Le mot de passe a été modifié avec succès !", Response::HTTP_OK);

            } else {
                return new JsonResponse("Le mot de passe actuel renseigné n'est pas valide", Response::HTTP_NOT_ACCEPTABLE);
            }
        } else {
            return new JsonResponse("Les nouveaux mot de passes saisis ne sont pas identiques", Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Route("/api/update_username", name="update_user_username", methods={"PATCH"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateUsernameInformations(Request $request, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {

        //get the data
        $data = json_decode($request->getContent(), true);

        //get users with jwt token storage
        $user = $this->getUser();

        $form = $this->createForm(UserUsernameUpdateType::class, $user);

        $form->submit($data);

        $validate = $validator->validate($user, null, 'PasswordUpdate');

        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }

        $user->setUpdatedAt(new \DateTime());

        $entityManager->persist($user);
        $entityManager->flush();

        return JsonResponse::fromJsonString($this->serializer->SimpleSerializerUserProfil($user, 'json'));
    }
}
