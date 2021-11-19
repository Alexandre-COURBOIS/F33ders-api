<?php

namespace App\Controller;

use App\Document\Champion;
use App\Document\FakePlayer;
use App\Document\Match;
use App\Document\Player;
use App\MongoRepository\PlayerRepository;
use App\Service\FunctionService;
use App\Service\PlayerService;
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
    private PlayerService $playerService;
    private DocumentManager $documentManager;

    public function __construct(RiotApiService  $riotApiService, FunctionService $functionService, SerializerService $serializerService,
                                DocumentManager $documentManager, PlayerService $playerService)
    {
        $this->riotApiService = $riotApiService;
        $this->functionService = $functionService;
        $this->serializerService = $serializerService;
        $this->playerService = $playerService;
        $this->documentManager = $documentManager;
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

            if (count($response) > 2) {
                //Format pour obtenir 20 matchs
                $response = array_slice($response["matches"], 0, 19, true);

                $response = ["matches" => $response];

                if (!empty($response) && $matchInDb === null) {

                    $player = new Player();

                    $player->setUsername($datas['username']);
                    $player->setUserHistory([$response]);

                    for ($i = 0; $i < count($response['matches']); $i++) {
                        $this->insertMatchHistory($response['matches'][$i]['gameId'], $datas['username']);
                    }

                    $dm->persist($player);
                    $dm->flush();

                    return new JsonResponse("Data has been set succesfully", Response::HTTP_CREATED);

                } elseif (!empty($matchInDb)) {
                    if ($matchInDb->getUserHistory()[0]['matches'][0]['timestamp'] === $response['matches'][0]['timestamp']) {

                        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($this->playerService->matchPlayedWithChampions($datas['username']), 'json'), Response::HTTP_OK);

                    } else {
                        $dm->createQueryBuilder(Player::class)->remove()->field('username')->equals($datas['username'])->getQuery()->execute();

                        $player = new Player();

                        $player->setUsername($datas['username']);
                        $player->setUserHistory([$response]);
                        $dm->persist($player);
                        $dm->flush();

                        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($this->playerService->matchPlayedWithChampions($datas['username']), 'json'), Response::HTTP_OK);
                    }
                } else {
                    return new JsonResponse("This username didn't return anything", Response::HTTP_NOT_FOUND);
                }
            } else {
                return new JsonResponse("This username didn't return anything", Response::HTTP_NOT_FOUND);
            }
        } else {
            return new JsonResponse("Please send available datas", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("api/userdata/get-history", name="userdata_gethistory")
     */
    public function getUserHistory(Request $request): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if (!empty($datas) && $datas != null) {

            return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($this->playerService->matchPlayedWithChampions($datas['username']), 'json'), Response::HTTP_OK);
        } else {
            return new JsonResponse("This data does not exist", Response::HTTP_BAD_REQUEST);
        }
    }

    function insertMatchHistory($gameId, $player)
    {
        if (!empty($gameId)) {

            $game = $this->riotApiService->getGameByid($gameId);

            if (!empty($game) && $game != null && count($game) > 2) {

                $match = new Match();

                $match->setMatchId($gameId);
                $match->setPlayer($player);
                $match->setMatch($game);

                $this->documentManager->persist($match);
                $this->documentManager->flush();
            } else {
                return new JsonResponse("No match available", Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * @Route("api/get-all-fakeplayer/database", name="get_all_fakeplayer_database")
     */
    public function getChampionAllChampion(DocumentManager $dm, Request $request): JsonResponse
    {
        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializerUserMongoDb($dm->getRepository(FakePlayer::class)->findAll(), 'json'), Response::HTTP_OK);
    }


}
