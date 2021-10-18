<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ContactController
 * @package App\Controller
 * @Route ("/contact", name="contact_")
 */
class ContactController extends AbstractController
{
    /**
     * @Route("/send/message", name="send_message")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function sendContactMessage(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if ($datas !== null && !empty($datas) && $datas) {

            $contact = new Contact();

            $form = $this->createForm(ContactType::class, $contact);

            $form->submit($datas);

            $validate = $validator->validate($contact, null, 'Contact');

            if (count($validate) !== 0) {
                foreach ($validate as $error) {
                    return new JsonResponse($error->getMessage(), Response::HTTP_BAD_REQUEST);
                }
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            return new JsonResponse("Nous avons bien receptionné votre message. Une réponse vous sera envoyée par email.", Response::HTTP_CREATED);

        } else {
            return new JsonResponse("Merci de renseigner correctement le formulaire", Response::HTTP_BAD_REQUEST);
        }
    }
}
