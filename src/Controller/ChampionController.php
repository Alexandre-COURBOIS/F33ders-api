<?php

namespace App\Controller;

use App\Document\Champion;
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

    public function __construct(RiotApiService $riotApiService, FunctionService $functionService, SerializerService $serializerService)
    {
        $this->riotApiService = $riotApiService;
        $this->functionService = $functionService;
        $this->serializerService = $serializerService;
    }

    /**
     * @Route("api/set-champion/database", name="set_champion_database")
     */
    public function setChampToDabase(DocumentManager $dm): JsonResponse
    {
        $datas = $this->riotApiService->getChampions();

        $allChamp = [];

        $champsInDatabase = $dm->getRepository(Champion::class)->findAll();

        if (count($datas['data']) !== count($champsInDatabase)) {

            $dm->createQueryBuilder(Champion::class)->remove()->getQuery()->execute();

            foreach ($datas['data'] as $champion) {

                $champ = new Champion();

                $champ->setChampionId($champion['id']);
                $champ->setKey($champion['key']);
                $champ->setName($champion['name']);
                $champ->setResume($champion['title']);
                $champ->setDescription($champion['blurb']);
                $champ->setAttack($champion['info']['attack']);
                $champ->setDefense($champion['info']['defense']);
                $champ->setMagic($champion['info']['magic']);
                $champ->setDifficulty($champion['info']['difficulty']);
                $champ->setTypes($champion['tags']);
                $champ->setImage($champion['image']);
                $champ->setImageUrl($champion['image']['full']);

                array_push($allChamp, $champion['name']);

                $dm->persist($champ);
            }

            $dm->flush();

            return new JsonResponse(count($allChamp) . " Champions has been set into database", Response::HTTP_CREATED);

        } else {
            return new JsonResponse("Data already up to date", Response::HTTP_BAD_REQUEST);
        }

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
