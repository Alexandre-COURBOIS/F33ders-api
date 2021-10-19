<?php

namespace App\Service;

use App\Document\Item;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ItemService
{
    private RiotApiService $riotApiService;
    private DocumentManager $documentManager;

    public function __construct(RiotApiService $riotApiService, DocumentManager $documentManager)
    {
        $this->riotApiService = $riotApiService;
        $this->documentManager = $documentManager;
    }

    public function setItemToDatabase(): JsonResponse
    {
        $datas = $this->riotApiService->getItems();

        $allItem = [];

        $itemInDatabase = $this->documentManager->getRepository(Item::class)->findAll();

        if (count($datas['data']) !== count($itemInDatabase)) {

            $this->documentManager->createQueryBuilder(Item::class)->remove()->getQuery()->execute();

            foreach ($datas['data'] as $key => $item) {

                $it = new Item();
                $it->setItemId($key);
                $it->setName($item['name']);
                $it->setItemName($item['colloq']);
                $it->setDescription($item['description']);
                $it->setImage($item['image']);
                $it->setImageUrl($item['image']['full']);

                array_push($allItem, $item['name']);

                $this->documentManager->persist($it);
            }

            $this->documentManager->flush();

            return new JsonResponse(count($allItem) . " Items has been set into database", Response::HTTP_CREATED);

        } else {
            return new JsonResponse("Data already up to date", Response::HTTP_BAD_REQUEST);
        }

    }
}