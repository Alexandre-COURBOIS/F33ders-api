<?php

namespace App\Controller;

use App\Document\Player;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RiotApiService;

class UserDataController extends AbstractController
{
    private RiotApiService $riotApiService;
    
    public function __construct(RiotApiService $riotApiService)
    {
        $this->riotApiService = $riotApiService;
    }
    
    /**
     * @Route("/userdata/insert-history/{username}", name="userdata_inserthistory")
     */
    public function insertHistory(string $username, DocumentManager $dm)
    {
        $response = $this->riotApiService->getUserApi($username);
        $content = $response->getContent();
        
        $player = new Player();
        $player->setId(1);
        $player->setUsername($username);
        $player->setUserHistory($content);
        
        $dm->persist($player);
        $dm->flush();
        
        
        
    }
}
