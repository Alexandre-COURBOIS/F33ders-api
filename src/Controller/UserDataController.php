<?php

namespace App\Controller;

use App\Document\Champion;
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
    private SerializerService $serializerService;

    public function __construct(RiotApiService $riotApiService, FunctionService $functionService, SerializerService $serializerService)
    {
        $this->riotApiService = $riotApiService;
        $this->functionService = $functionService;
        $this->serializerService = $serializerService;
    }

    /**
     * @Route("api/userdata/insert-history", name="userdata_inserthistory")
     */
    public function insertHistory(Request $request, DocumentManager $dm): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if (!empty($datas) && !empty($datas['username'])) {

            $matchInDb = $dm->getRepository(Player::class)->findOneBy(['username' => $datas['username']]);

            $response = $this->riotApiService->getUserApi($datas['username']);

            if (!empty($response) && $matchInDb === null) {

                $player = new Player();

                $player->setUsername($datas['username']);
                $player->setUserHistory([$response]);
                $dm->persist($player);
                $dm->flush();

                return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($dm->getRepository(Player::class)->findAll(), 'json'), Response::HTTP_CREATED);

            } elseif (!empty($matchInDb)) {
                if ($matchInDb->getUserHistory()[0]['matches'][0]['timestamp'] === $response['matches'][0]['timestamp']) {

                    return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($dm->getRepository(Player::class)->findAll(), 'json'), Response::HTTP_OK);

                } else {
                    $dm->createQueryBuilder(Player::class)->remove()->field('username')->equals($datas['username'])->getQuery()->execute();

                    $player = new Player();

                    $player->setUsername($datas['username']);
                    $player->setUserHistory([$response]);
                    $dm->persist($player);
                    $dm->flush();

                    return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($dm->getRepository(Player::class)->findAll(), 'json'), Response::HTTP_CREATED);
                }
            } else {
                return new JsonResponse("This username didn't return anything", Response::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse("Username unavailable", Response::HTTP_BAD_REQUEST);
        }
    }

}
