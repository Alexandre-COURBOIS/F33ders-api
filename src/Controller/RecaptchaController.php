<?php

namespace App\Controller;

use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecaptchaController extends AbstractController
{
    /**
     * @Route("/recaptcha/validate", name="recaptcha_validate")
     * @param Request $request
     * @return Response
     */
    public function verifyRecaptcha(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);

        $recaptcha = new ReCaptcha($_ENV['GOOGLE_RECAPTCHA_SECRET']);

        $response = $recaptcha->verify($data['recaptcha']);

        return new JsonResponse($response->isSuccess());
    }
}
