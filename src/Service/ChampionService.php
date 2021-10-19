<?php

namespace App\Service;

use App\Document\Champion;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ChampionService
{
    private RiotApiService $riotApiService;
    private DocumentManager $documentManager;

    public function __construct(RiotApiService $riotApiService, DocumentManager $documentManager)
    {
        $this->riotApiService = $riotApiService;
        $this->documentManager = $documentManager;
    }

    public function setChampInDatabase(): JsonResponse
    {
        $datas = $this->riotApiService->getChampions();

        $allChamp = [];

        $champsInDatabase = $this->documentManager->getRepository(Champion::class)->findAll();

        if (count($datas['data']) !== count($champsInDatabase)) {

            $this->documentManager->createQueryBuilder(Champion::class)->remove()->getQuery()->execute();

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

                $this->documentManager->persist($champ);
            }

            $this->documentManager->flush();

            return new JsonResponse(count($allChamp) . " Champions has been set into database", Response::HTTP_CREATED);

        } else {
            return new JsonResponse("Data already up to date", Response::HTTP_BAD_REQUEST);
        }
    }

}