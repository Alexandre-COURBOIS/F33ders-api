<?php

namespace App\Controller;

use App\Form\UserInformationsUpdateByAdminType;
use App\Form\UserPasswordUpdateByAdminType;
use App\Repository\UserRepository;
use App\Service\FunctionService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminController extends AbstractController
{

    private SerializerService $serializer;
    private FunctionService $functionService;

    public function __construct(SerializerService $serializerService, FunctionService $functionService)
    {
        $this->serializer = $serializerService;
        $this->functionService = $functionService;
    }

    /**
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @Route("/api/get_all_user", name="api_get_all_user", methods={"GET"})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function getAllUserInformations(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        if ($users) {
            return JsonResponse::fromJsonString($this->serializer->SimpleSerializer($users, 'json'));
        } else {
            return new JsonResponse("Aucune informations", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @Route("/api/ban_user", name="api_ban_user", methods={"POST"})
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     */
    public function banUser(UserRepository $userRepository, EntityManagerInterface $em, Request $request): Response
    {
        $userSender = $this->getUser();

        $datas = json_decode($request->getContent(), true);

        if ($datas && $datas['informations'] && $datas['id']) {

            $user = $userRepository->findOneBy(['id' => $datas['id']]);

            if ($datas['informations'] == "ban") {
                $user->setIsBanned(true);

                $em->persist($user);
                $em->flush();

                return new JsonResponse("Le compte a bien été suspendu", Response::HTTP_OK);

            } elseif ($datas['informations'] == "unban") {
                $user->setIsBanned(false);

                $em->persist($user);
                $em->flush();

                return new JsonResponse("Le compte a bien été rétablis", Response::HTTP_OK);
            }

        } else {
            return new JsonResponse("Datas are not available", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @Route("/api/activate_user_account_admin", name="api_activate_user_account_admin", methods={"POST"})
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     */
    public function activateAccount(UserRepository $userRepository, EntityManagerInterface $em, Request $request): Response
    {
        $userSender = $this->getUser();

        $datas = json_decode($request->getContent(), true);

        if ($datas && $datas['informations'] && $datas['id']) {

            $user = $userRepository->findOneBy(['id' => $datas['id']]);

            if ($datas['informations'] == "activate") {
                $user->setIsActive(true);

                $em->persist($user);
                $em->flush();

                return new JsonResponse("Le compte a bien été activé", Response::HTTP_OK);

            } elseif ($datas['informations'] == "unactivate") {
                $user->setIsActive(false);

                $em->persist($user);
                $em->flush();

                return new JsonResponse("Le compte a bien été désactivé", Response::HTTP_OK);
            }
        } else {
            return new JsonResponse("Datas are not available", Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @Route("/api/delete_user", name="api_delete_user_account_admin", methods={"POST"})
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUser(UserRepository $userRepository, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if ($datas && $datas['id']) {

            $user = $userRepository->findOneBy(['id' => $datas['id']]);

            if ($user) {

                $em->remove($user);
                $em->flush();

                return new JsonResponse('User has been deleted successfully', Response::HTTP_OK);

            } else {
                return new JsonResponse("User does not exist", Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse("Datas unavailable", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("api/admin_update-user-password", name="admin_update_password", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @param Request $request
     * @param UserRepository $repo
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function adminUpdatePasswordOfUser(Request                $request, UserRepository $repo, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder,
                                              EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $repo->findOneBy(['id' => $data['id']]);

        if ($data['password'] === $data['verifNewPassword']) {

            $form = $this->createForm(UserPasswordUpdateByAdminType::class, $user);

            $form->submit($data);

            $validate = $validator->validate($user, null, 'PasswordUpdateByAdmin');

            if (count($validate) !== 0) {
                foreach ($validate as $error) {
                    return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                }
            }

            $hashword = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hashword);
            $user->setUpdatedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            return new JsonResponse("Le mot de passe a été modifié avec succès !");

        } else {
            return new JsonResponse("Les mot de passe ne correspondent pas", Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Route("api/admin_update_user_informations", name="admin_update_user_informations", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @param Request $request
     * @param UserRepository $repo
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function adminUpdateInformationsUser(Request $request, UserRepository $repo, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data && $data['id'] && $data['username'] && $data['email']) {

            $user = $repo->findOneBy(['id' => $data['id']]);

            $form = $this->createForm(UserInformationsUpdateByAdminType::class, $user);

            $form->submit($data);

            $validate = $validator->validate($user, null, 'InformationUpdateAdmin');

            if (count($validate) !== 0) {
                foreach ($validate as $error) {
                    return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                }
            }

            $em->persist($user);
            $em->flush();

            return new JsonResponse("Les données ont été mise à jour avec succès !");

        } else {
            return new JsonResponse("Les données soumisent ne sont pas valides", Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Route("api/admin_update_password_automatically", name="admin_update_password_automatically", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", message="You can't reach this method")
     * @param Request $request
     * @param UserRepository $repo
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @return JsonResponse
     * @throws TransportExceptionInterface
     */
    public function adminUpdatePasswordAutomaticaly(Request $request, UserRepository $repo, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data && $data['id'] && $repo->findOneBy(['id' => $data['id']])) {

            $password = $this->functionService->randomPassword();

            $toEncode = [
                'password' => $password
            ];

            $user = $repo->findOneBy(['id' => $data['id']]);

            $form = $this->createForm(UserPasswordUpdateByAdminType::class, $user);

            $form->submit($toEncode);

            $validate = $validator->validate($user, null, 'PasswordUpdateByAdmin');

            if (count($validate) !== 0) {
                foreach ($validate as $error) {
                    return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                }
            }

            $hashword = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hashword);
            $user->setUpdatedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
                ->from('support@f33ders.fr')
                ->to($user->getEmail())
                ->subject("Votre nouveau mot de passe")
                ->htmlTemplate('admin/automatic_password.html.twig')
                ->context([
                    'user' => $user,
                    'password' => $password
                ]);

            $mailer->send($email);

            return new JsonResponse("Le mot de passe a été modifié avec succès ! ", Response::HTTP_OK);

        } else {
            return new JsonResponse("Impossible d'executer cette requête", Response::HTTP_BAD_REQUEST);
        }
    }

}
