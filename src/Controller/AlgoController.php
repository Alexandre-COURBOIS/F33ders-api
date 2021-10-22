<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlgoController extends AbstractController
{
    /**
     * @Route("/algo", name="algo")
     */
    public function index(): Response
    {
        return $this->render('algo/index.html.twig', [
            'controller_name' => 'AlgoController',
        ]);
    }
}
