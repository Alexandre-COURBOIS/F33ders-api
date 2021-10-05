<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegisterType;
use Doctrine\Bundle\MongoDBBundle\ManagerConfigurator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register/user", name="register_user")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param MailerInterface $mailer
     * @return JsonResponse
     * Methode permettant l'inscription de nouveaux utilisateurs
     * @throws TransportExceptionInterface
     */
    public function userRegister(Request $request, ValidatorInterface $validator, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!empty($data)) {

            $user = new User();

            $form = $this->createForm(UserRegisterType::class, $user);

            $form->submit($data);

            $validate = $validator->validate($user, null, 'Register');

            if (count($validate) !== 0) {
                foreach ($validate as $error) {
                    return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                }
            }

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
                ->from('support.web@f33ders.com')
                ->to($user->getEmail())
                ->subject('Activation de votre compte F33ders !')
                ->htmlTemplate('user_account/activating_account.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($email);

            return new JsonResponse('Vous êtes bien inscrit ! Merci d\'activer votre compte via l\'email qui vient de vous être envoyé afin de pouvoir vous connecter', Response::HTTP_CREATED);
        } else {
            return new JsonResponse("There is no informations to treat", Response::HTTP_BAD_REQUEST);
        }
    }
}
