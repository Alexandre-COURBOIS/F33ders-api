<?php

namespace App\Controller;

use App\Document\Champion;
use App\Service\ChampionService;
use App\Service\FunctionService;
use App\Service\RiotApiService;
use App\Service\SerializerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Utility\IdentifierFlattener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChampionController extends AbstractController
{
    private RiotApiService $riotApiService;
    private FunctionService $functionService;
    private SerializerService $serializerService;
    private ChampionService $championService;

    public function __construct(RiotApiService $riotApiService, FunctionService $functionService, SerializerService $serializerService, ChampionService $championService)
    {
        $this->riotApiService       = $riotApiService;
        $this->functionService      = $functionService;
        $this->serializerService    = $serializerService;
        $this->championService      = $championService;
    }

    /**
     * @Route("api/set-champion/database", name="set_champion_database")
     */
    public function setChampToDatabase(): JsonResponse
    {
        return $this->championService->setChampInDatabase();
    }

    /**
     * @Route("api/get-all-champion/database", name="get_all_champion_database")
     */
    public function getChampionAllChampion(DocumentManager $dm, Request $request): JsonResponse
    {
        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($dm->getRepository(Champion::class)->findAll(), 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("api/get-champion/database", name="get_champion_database")
     */
    public function getChampionByKey(DocumentManager $dm, Request $request): JsonResponse
    {
        $datas = json_decode($request->getContent(), true);

        if (!empty($datas) && !empty($datas['key'])) {

            return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($dm->getRepository(Champion::class)->findOneBy(['key' => $datas['key']]), 'json'), Response::HTTP_OK);

        } else {
            return new JsonResponse("Data sent is unavaible", Response::HTTP_BAD_REQUEST);
        }
    }
}
