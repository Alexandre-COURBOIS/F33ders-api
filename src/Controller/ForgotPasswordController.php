<?php

namespace App\Controller;

use App\Form\UserPasswordType;
use App\Repository\UserRepository;
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

class ForgotPasswordController extends AbstractController
{
    /**
     * @Route("/forgot/password", name="forgot_password", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @return JsonResponse
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    public function sendMailForgotPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $em, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['email' => $data['email']]);

        if ($user) {

            $user->setResetToken(rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '='));
            $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $user->setResetTokenAt($date);
            $user->setUpdatedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
                ->from('support.web@f33ders.com')
                ->to($user->getEmail())
                ->subject('Mot de passe oublié ?')
                ->htmlTemplate('forgot_password/forgot_password.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($email);

            return new JsonResponse('Un email vient de vous êtres envoyé !', Response::HTTP_OK);

        } else {
            return new JsonResponse("Email incorrect, ou innexistant.", RESPONSE::HTTP_BAD_REQUEST);
        }

    }

    /**
     * @Route("/reset/password", name="reset_password", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function resetForgotPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $content = json_decode($request->getContent());

        $token = $content->token;
        $date = $content->date;

        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if ($user) {
            if ($user->getResetToken() === $token && $user->getResetToken() && $user->getResetTokenAt()) {

                $formatStartDate = $user->getResetTokenAt();
                $formatCurrentDate = \DateTime::createFromFormat("Y-m-d H:i:s", $date);

                $interval = $formatStartDate->diff($formatCurrentDate);

                if ($interval->d === 0 && $interval->m === 0 && $interval->h === 0) {
                    if ((int)$interval->i < 30) {
                        return new JsonResponse($user->getEmail(), Response::HTTP_OK);
                    } else {
                        $user->setResetToken(null);
                        $user->setResetTokenAt(null);
                        $em->persist($user);
                        $em->flush();
                        return new JsonResponse("Le délais pour changer votre mot de passe à expirer, merci de renouveler la demande.", RESPONSE::HTTP_BAD_REQUEST);
                    }
                } else {
                    $user->setResetToken(null);
                    $user->setResetTokenAt(null);
                    $em->persist($user);
                    $em->flush();
                    return new JsonResponse("Le délais pour changer votre mot de passe à expirer, merci de renouveler la demande.", RESPONSE::HTTP_BAD_REQUEST);
                }
            } else {
                return new JsonResponse("Cette url n'existe pas ou est incorrect.", RESPONSE::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse("Cette url n'existe pas ou est incorrect.", RESPONSE::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/reset-password/update-password", name="update_password_user", methods={"POST"})
     * @param Request $request
     * @param userRepository $userRepository
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param MailerInterface $mailer
     * @return JsonResponse
     * @throws TransportExceptionInterface
     */
    public function updateForgotPasswordUser(Request $request, userRepository $userRepository, ValidatorInterface $validator, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, MailerInterface $mailer): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if ($datas !== null && $datas) {

            $email = $datas['email'];

            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {

                $newPassword = $datas['password'];

                $verifNewPassword = $datas['verifPassword'];

                if ($newPassword === $verifNewPassword) {

                    $form = $this->createForm(UserPasswordType::class, $user);

                    $form->submit($datas);

                    $validate = $validator->validate($user, null, 'PasswordUpdate');

                    if (count($validate) !== 0) {
                        foreach ($validate as $error) {
                            return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                        }
                    }

                    $hashword = $encoder->encodePassword($user, $user->getPassword());

                    $user->setPassword($hashword);
                    $user->setResetTokenAt(null);
                    $user->setResetToken(null);
                    $user->setUpdatedAt(new \DateTime());

                    $em->persist($user);
                    $em->flush();

                    $email = (new TemplatedEmail())
                        ->from('support.web@f33ders.co')
                        ->to($user->getEmail())
                        ->subject('Mise à jour de vos informations')
                        ->htmlTemplate('email/informations_update.html.twig')
                        ->context([
                            'user' => $user,
                        ]);

                    $mailer->send($email);

                    return new JsonResponse('Le mot de passe a bien été mis à jour !', Response::HTTP_OK);

                } else {
                    return new JsonResponse("Les deux nouveaux mot de passe ne correspondent pas !", Response::HTTP_BAD_REQUEST);
                }
            } else {
                return new JsonResponse("Cet email ne correspond à aucun utilisateur", Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse("Merci de renseigner des informations !", Response::HTTP_BAD_REQUEST);
        }
    }

}
