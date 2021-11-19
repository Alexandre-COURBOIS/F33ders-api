<?php

namespace App\Service;

use App\Document\Champion;
use App\Document\FakePlayer;
use App\Document\Item;
use App\Document\Player;
use App\Document\Match;
use Doctrine\ODM\MongoDB\DocumentManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerService
{

    private DocumentManager $documentManager;
    private RiotApiService $riotApiService;
    private SerializerService $serializerService;

    public function __construct(DocumentManager $documentManager, RiotApiService $riotApiService, SerializerService $serializerService)
    {
        $this->documentManager = $documentManager;
        $this->riotApiService = $riotApiService;
        $this->serializerService = $serializerService;
    }

    public function matchPlayedWithChampions($playerName)
    {
        if (!empty($playerName)) {

            $allMatchPlayer = $this->documentManager->getRepository(Player::class)->findOneBy(['username' => $playerName]);

            if (!empty($allMatchPlayer)) {

                $storeMatchPlayed = [];

                foreach ($allMatchPlayer->getUserHistory()[0]['matches'] as $match) {
                    $storeMatchPlayed['player']['id'] = $allMatchPlayer->getId();
                    $storeMatchPlayed['player']['username'] = $allMatchPlayer->getUsername();

                    $storeMatchPlayed['match'][] = ["currentUserChampion" => $match['champion'], "game" => ["gameId" => $match['gameId']]];
                }

                $gameInformations = [];

                for ($i = 0; $i < count($storeMatchPlayed['match']); $i++) {

                    $currentGame = $this->documentManager->getRepository(Match::class)->findOneBy(['matchId' => $storeMatchPlayed['match'][$i]['game']['gameId']]);

                    if ($currentGame !== null) {

                        if (count($currentGame->getMatch()) > 2) {

                            foreach ($currentGame->getMatch()[11] as $participant) {

                                $champ = $this->documentManager->getRepository(Champion::class)->findOneBy(['key' => $participant["championId"]]);

                                $item = $this->getItems($participant["stats"]["item0"], $participant["stats"]["item1"], $participant["stats"]["item2"],
                                    $participant["stats"]["item3"], $participant["stats"]["item4"], $participant["stats"]["item5"], $participant["stats"]["item6"]);

                                $gameInformations['teams'][] =
                                    ["champ" =>
                                        ["informations" =>
                                            [
                                                'win' => $participant['stats']['win'],
                                                'key' => $champ->getKey(),
                                                'championId' => $champ->getchampionId(),
                                                'imageUrl' => $champ->getImageUrl()
                                            ],
                                            "stats" =>
                                                [
                                                    "attack" => $champ->getAttack(),
                                                    "defense" => $champ->getDefense(),
                                                    "magic" => $champ->getMagic(),
                                                    "difficulty" => $champ->getDifficulty(),
                                                ]
                                        ],
                                        "Items" =>
                                            [
                                                $item[0][0] ? [str_replace('é', 'e', $item[0][0]->getName()), $item[0][0]->getItemId(), $item[0][0]->getItemName(), $item[0][0]->getImageUrl()] : null,
                                                $item[0][1] ? [str_replace('é', 'e', $item[0][1]->getName()), $item[0][1]->getItemId(), $item[0][1]->getItemName(), $item[0][1]->getImageUrl()] : null,
                                                $item[0][2] ? [str_replace('é', 'e', $item[0][2]->getName()), $item[0][2]->getItemId(), $item[0][2]->getItemName(), $item[0][2]->getImageUrl()] : null,
                                                $item[0][3] ? [str_replace('é', 'e', $item[0][3]->getName()), $item[0][3]->getItemId(), $item[0][3]->getItemName(), $item[0][3]->getImageUrl()] : null,
                                                $item[0][4] ? [str_replace('é', 'e', $item[0][4]->getName()), $item[0][4]->getItemId(), $item[0][4]->getItemName(), $item[0][4]->getImageUrl()] : null,
                                                $item[0][5] ? [str_replace('é', 'e', $item[0][5]->getName()), $item[0][5]->getItemId(), $item[0][5]->getItemName(), $item[0][5]->getImageUrl()] : null,
                                                $item[0][6] ? [str_replace('é', 'e', $item[0][6]->getName()), $item[0][6]->getItemId(), $item[0][6]->getItemName(), $item[0][6]->getImageUrl()] : null,
                                            ],
                                        "Player" =>
                                            [
                                                "Kills" => $participant['stats']['kills'],
                                                "Deaths" => $participant['stats']['deaths'],
                                                "Assists" => $participant['stats']['assists'],
                                                "largestKillingSpree" => $participant['stats']['largestKillingSpree'],
                                                "totalDamageDealt" => $participant['stats']['totalDamageDealt'],
                                                "wardsPlaced" => $participant['stats']['wardsPlaced'],
                                                "wardsKilled" => $participant['stats']['wardsKilled'],
                                                "visionScore" => $participant['stats']['visionScore'],
                                                "goldEarned" => $participant['stats']['goldEarned'],
                                                "totalMinionsKilled" => $participant['stats']['totalMinionsKilled'],
                                                "champLevel" => $participant['stats']['champLevel'],
                                            ]
                                    ];
                            }

                            array_push($storeMatchPlayed['match'][$i]["game"], $gameInformations);
                            $gameInformations = [];
                        } else {
                            return new JsonResponse('Aucun match enregistre correspondant a ce profil', Response::HTTP_NOT_FOUND);
                        }
                    } else {
                        return new JsonResponse('Aucun match enregistre correspondant a ce profil 2', Response::HTTP_NOT_FOUND);
                    }
                }
                return $storeMatchPlayed;
            } else {
                return new JsonResponse("No match available", Response::HTTP_NOT_FOUND);
            }
        } else {
            return new JsonResponse("No user available", Response::HTTP_NOT_FOUND);
        }
    }

    function getItems($item, $item2, $item3, $item4, $item5, $item6, $item7): array
    {
        $outputTable = [];

        $item = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item]);
        $item2 = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item2]);
        $item3 = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item3]);
        $item4 = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item4]);
        $item5 = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item5]);
        $item6 = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item6]);
        $item7 = $this->documentManager->getRepository(Item::class)->findOneBy(['itemId' => $item7]);

        $it = [$item, $item2, $item3, $item4, $item5, $item6, $item7];

        array_push($outputTable, $it);

        return $outputTable;
    }

    public function insertFakePlayerData(): JsonResponse
    {

        $faker = Factory::create();

        $bool = [
            0 => 0,
            1 => 1,
        ];

        $roles = [
            0 => 'TOP',
            1 => 'JUNGLE',
            2 => 'MID',
            3 => 'ADC',
            4 => 'SUPPORT'
        ];

        for ($i = 0; $i < 100; $i++) {

            $fakePlayer = new FakePlayer();

            $fakePlayer->setMainRole($roles[rand(0, 4)]);
            $fakePlayer->setSecondaryRole($roles[rand(0, 4)]);
            $fakePlayer->setAgressivity($bool[rand(0, 1)]);
            $fakePlayer->setVulnerable($bool[rand(0, 1)]);
            $fakePlayer->setTeamfighter($bool[rand(0, 1)]);
            $fakePlayer->setCcer($bool[rand(0, 1)]);
            $fakePlayer->setMoneyPlayer($bool[rand(0, 1)]);
            $fakePlayer->setUsername($faker->userName);


            $this->documentManager->persist($fakePlayer);
            $this->documentManager->flush();
        }

        return new JsonResponse("Fake Players Added Succesfully");
    }

    function getAllPlayer(): array
    {
        return $this->documentManager->getRepository(Player::class)->findAll();
    }

    public function getallFakePlayer(DocumentManager $dm, Request $request): JsonResponse
    {
        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializerUserMongoDb($dm->getRepository(FakePlayer::class)->findAll(), 'json'), Response::HTTP_OK);
    }
}
