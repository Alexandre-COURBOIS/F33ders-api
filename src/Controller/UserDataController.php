<?php

namespace App\Controller;

use App\Document\Player;
use App\MongoRepository\PlayerRepository;
use App\Service\FunctionService;
use App\Service\SerializerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RiotApiService;

class UserDataController extends AbstractController
{
    private RiotApiService $riotApiService;
    private FunctionService $functionService;

    public function __construct(RiotApiService $riotApiService, FunctionService $functionService)
    {
        $this->riotApiService = $riotApiService;
        $this->functionService = $functionService;
    }

    /**
     * @Route("api/userdata/insert-history", name="userdata_inserthistory")
     */
    public function insertHistory(string $username, DocumentManager $dm, Request $request): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if (!empty($datas) && !empty($datas['username'])) {

            $response = $this->riotApiService->getUserApi($datas['username']);

            if (!empty($response)) {

                $player = new Player();

                $player->setUsername($username);
                $player->setUserHistory([$response]);
                $dm->persist($player);
                $dm->flush();

                return new JsonResponse("Datas has been added successfully", Response::HTTP_CREATED);

            } else {
                return new JsonResponse("This username didn't return anything", Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse("Username unavailable", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("api/userdata/get-history/", name="get_user_data")
     */
    public function getUserHistory(DocumentManager $dm, SerializerService $serializerService, Request $request): JsonResponse
    {
        return JsonResponse::fromJsonString($serializerService->SimpleSerializer($dm->getRepository(Player::class)->findOneBy(['username' => json_decode($request->getContent(), true)['username']]), 'json'));
    }


}
