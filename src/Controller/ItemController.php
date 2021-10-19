<?php

namespace App\Controller;

use App\Document\Item;
use App\Service\FunctionService;
use App\Service\ItemService;
use App\Service\RiotApiService;
use App\Service\SerializerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{

    private RiotApiService $riotApiService;
    private FunctionService $functionService;
    private SerializerService $serializerService;
    private ItemService  $itemService;

    public function __construct(RiotApiService $riotApiService, FunctionService $functionService, SerializerService $serializerService, ItemService $itemService)
    {
        $this->riotApiService       = $riotApiService;
        $this->functionService      = $functionService;
        $this->serializerService    = $serializerService;
        $this->itemService          = $itemService;
    }

    /**
     * @Route("api/set-item/database", name="set_item_database")
     */
    public function setItemToDatabase(DocumentManager $dm): JsonResponse
    {
        return $this->itemService->setItemToDatabase();
    }
}
