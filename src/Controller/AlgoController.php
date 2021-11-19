<?php

namespace App\Controller;

use App\Document\Match;
use App\Document\Player;
use App\Service\PlayerService;
use App\Service\RiotApiService;
use App\Service\SerializerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class AlgoController extends AbstractController
{
    private RiotApiService $riotApiService;
    private PlayerService $playerService;
    private SerializerService $serializerService;
    private DocumentManager $dm;

    public function __construct(RiotApiService $riotApiService, PlayerService $playerService, SerializerService $serializerService, DocumentManager $dm)
    {
        $this->riotApiService = $riotApiService;
        $this->playerService = $playerService;
        $this->serializerService = $serializerService;
        $this->dm = $dm;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("api/algo/find-team", name="algo_findteam")
     */
    public function findTeam(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $data = $this->process($username);
        $arrayLane = [
            "top" => [],
            "jungle" => [],
            "mid" => [],
            'adc' => [],
            'support' => []
        ];
//        $allPLayer = $this->playerService->getAllPlayer();
        $allPlayer = $this->playerService->getallFakePlayer();

        if ($data['mainRole'] == 'ADC') {
            $arrayLane['adc'] = $data;
            foreach ($allPlayer as $player) {
                $searchPlayer = $this->process($player->getUsername());
                if ($searchPlayer['mainRole'] == "SUPPORT") {

                    if (!empty($arrayLane['top']) || !empty($arrayLane['jungle'])) {
                        if ($arrayLane['top']['ccer'] == false || $arrayLane['jungle']['ccer'] == false) {
                            if ($searchPlayer['agressivity'] == $data['agressivty'] && $searchPlayer['ccer'] == true) {
                                $arrayLane['support'] = $searchPlayer;
                            }
                        } else {
                            if ($searchPlayer['agressivity'] == $data['agressivty']) {
                                $arrayLane['support'] = $searchPlayer;
                            }
                        }
                    }
                }
            }
        } elseif ($data['mainRole'] == 'SUPPORT') {
            $arrayLane['support'] = $data;
            foreach ($allPlayer as $player) {
                $searchPlayer = $this->process($player->getUsername());
                if ($searchPlayer['mainRole'] == "ADC") {
                    if ($searchPlayer['agressivity'] == $data['agressivity']) {
                        $arrayLane['support'] = $searchPlayer;
                    }
                }
            }
        } elseif ($data['mainRole'] == 'JUNGLE') {
            //find mid
            if (empty($arrayLane['mid'])) {
                foreach ($allPlayer as $player) {
                    $searchPlayer = $this->process($player->getUsername());
                    if ($data['agressivity'] == $searchPlayer['agressivity']) {
                        if ($data['ccer'] == false) {
                            if ($searchPlayer['ccer'] == true) {
                                $arrayLane['mid'] = $searchPlayer;
                            }
                        } else {
                            $arrayLane['mid'] = $searchPlayer;
                        }
                    }
                }
            }

        } elseif ($data['mainRole'] == 'MID') {
            $arrayLane['mid'] = $data;
            $arrayLane['mid']['username'] = $username;
            for ($i = 0; $i < count($arrayLane); $i++) {
                foreach ($allPlayer as $player) {
//                    $searchPlayer = $this->process($player->getUsername());
                    $searchPlayer = $player;
                    if (empty($arrayLane['jungle'])) {
                        if($searchPlayer->getMainRole() == 'JUNGLE') {
                            if ($data['vulnerable'] == true) {
                                if ($searchPlayer->getAgressivity() == "1") {
                                    if ($data['ccer'] == false) {
                                        if ($searchPlayer->getCcer() == "1") {
                                            $arrayLane['jungle'] = $searchPlayer;
                                            break;
                                        }
                                    } else {
                                        $arrayLane['jungle'] = $searchPlayer;
                                        break;
                                    }
                                }
                            } elseif (($data['agressivity'] == true && $searchPlayer->getAgressivity() == "1") || ($data['agressivity'] == false && $searchPlayer->getAgressivity() == "")) {
                                if ($data['ccer'] == false) {
                                    if ($searchPlayer->getCcer() == "1") {
                                        $arrayLane['jungle'] = $searchPlayer;
                                        break;
                                    }
                                } else {
                                    $arrayLane['jungle'] = $searchPlayer;
                                    break;
                                }
                            }
                        }
                    } elseif(empty($arrayLane['top'])) {
                        if($searchPlayer->getMainRole() == 'TOP') {
                            if ($arrayLane['jungle']->getAgressivity() == "1" && $arrayLane['mid']['agressivity'] == true) {
                                if ($searchPlayer->getTeamfighter() == true) {
                                    $arrayLane['top'] = $searchPlayer;
                                    break;
                                }
                            } elseif($searchPlayer->getMoneyPlayer() == "1") {
                                $arrayLane['top'] = $searchPlayer;
                                break;
                            }
                        }
                    } elseif(empty($arrayLane['support'])) {
                        if($searchPlayer->getMainRole() == 'SUPPORT') {
                            if(!$arrayLane['top']['ccer'] || !$arrayLane['mid']['ccer'] || !$arrayLane['jungle']['ccer']) {
                                if($searchPlayer->getCcer() == "1") {
                                    $arrayLane['support'] = $searchPlayer;
                                    break;
                                }
                            }
                        } else {
                            $arrayLane['support'] = $searchPlayer;
                            break;
                        }
                    } elseif(empty($arrayLane['adc'])) {
                        if($searchPlayer->getMainRole() == 'ADC') {
                            if(($searchPlayer->getAgressivity() == "1" && $arrayLane['support']->getAgressivity() == "1") || ($searchPlayer->getAgressivity() == "" && $arrayLane['support']['agressivity'] == false)) {
                                $arrayLane['adc'] = $searchPlayer;
                                break;
                            }
                        }
                    }
                }
            }
        } elseif ($data['mainRole'] == 'TOP') {
            // fill
        }
        
        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($arrayLane, 'json'), Response::HTTP_OK);
    }


    private function process($username): array
    {
        if (!empty($username)) {
            $data = $this->getAvgStats($username);
            $role = $this->getRole($username);

            if (!empty($role['main'])) {
                $player['mainRole'] = $role['main'];
            }
            if (!empty($role['secondary'])) {
                $player['secondaryRole'] = $role['secondary'];
            }
            if (isset($data['avgFb']) && isset($data['avgFba'])) {
                if (($data['avgFb'] + $data['avgFba']) > 0.2) {
                    $player['agressivity'] = true;
                } else {
                    $player['agressivity'] = false;
                }
            } else {
                $player['agressivity'] = false;
            }


            if ($data['avgDeaths'] > 3) {
                $player['vulnerable'] = true;
            } else {
                $player['vulnerable'] = false;
            }
            if (($data['avgKill'] + $data['avgAssists']) > 15) {
                $player['teamfighter'] = true;
            } else {
                $player['teamfighter'] = false;
            }
            if (isset($data['avgTimeCc'])) {
                if ($data['avgTimeCc'] > 20) {
                    $player['ccer'] = true;
                } else {
                    $player['ccer'] = false;
                }
            } else {
                $player['ccer'] = false;
            }

            if (isset($data['avgGold'])) {
                if ($data['avgGold'] > 10000) {
                    $player['moneyPlayer'] = true;
                } else {
                    $player['moneyPlayer'] = false;
                }
            } else {
                $player['moneyPlayer'] = false;
            }

            return $player;
        }

    }

    public function getRole($username): array
    {
        $historique = $this->riotApiService->getUserApi($username);

        $tabRoles = [];
        foreach ($historique['matches'] as $match) {
            if ($match['role'] == "DUO_SUPPORT") {
                $tabRoles[] = "SUPPORT";
            } elseif ($match['role'] == "DUO_CARRY") {
                $tabRoles[] = "ADC";
            } elseif ($match['lane'] !== "BOTTOM" && $match['lane'] !== "NONE") {
                $tabRoles[] = $match['lane'];
            }
        }

        $occurences = array_count_values($tabRoles);

        $mainRoles = [];
        foreach ($occurences as $key => $value) {
            if ($value == max($occurences)) {
                if (!isset($mainRoles['main'])) {
                    $mainRoles['main'] = $key;
                } else {
                    $mainRoles['secondary'] = $key;
                }
                unset($occurences[$key]);
            }
            if (count($mainRoles) == 2) {
                break;
            }
        }
//        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($mainRoles, 'json'), Response::HTTP_OK);
//        return new JsonResponse($mainRoles, Response::HTTP_OK);
        return $mainRoles;

    }

    public function getAvgStats($username): array
    {
        $allMatchPlayer = $this->dm->getRepository(Player::class)->findOneBy(['username' => $username]);
        $games = [];
        foreach ($allMatchPlayer->getUserHistory()[0]['matches'] as $match) {
            $games[] = $this->dm->getRepository(Match::class)->findOneBy(['matchId' => $match['gameId']]);
        }
        
        $userData = [];
        $i = 0;
        foreach ($games as $match) {
            if (isset($match)) {
                $idParticipant = $this->getParticipantId($match->getMatch()[12], $username);

                foreach ($match->getMatch()[11] as $userGameInfo) {
                    if ($userGameInfo['participantId'] == $idParticipant) {
                        $userData[$i]['kills'] = $userGameInfo['stats']['kills'];
                        $userData[$i]['deaths'] = $userGameInfo['stats']['deaths'];
                        $userData[$i]['assists'] = $userGameInfo['stats']['assists'];
                        $userData[$i]['firstBloodKill'] = $userGameInfo['stats']['firstBloodKill'];
                        $userData[$i]['firstBloodAssist'] = $userGameInfo['stats']['firstBloodAssist'];
//                    $userData[$i]['firstTowerKill'] = $userGameInfo['stats']['firstTowerKill'];
//                    $userData[$i]['firstTowerAssist'] = $userGameInfo['stats']['firstTowerAssist'];
                        $userData[$i]['visionScore'] = $userGameInfo['stats']['visionScore'];
                        $userData[$i]['timeCCingOthers'] = $userGameInfo['stats']['timeCCingOthers'];
                        $userData[$i]['goldEarned'] = $userGameInfo['stats']['goldEarned'];
                        $userData[$i]['longestTimeSpentLiving'] = $userGameInfo['stats']['longestTimeSpentLiving'];
                    }
                }
                $i++;
            }

        }

        $kills = [];
        $deaths = [];
        $assists = [];
        foreach ($userData as $data) {
            $kills[] = $data['kills'];
            $deaths[] = $data['deaths'];
            $assists[] = $data['assists'];
            $fb[] = $data['firstBloodKill'];
            $fba[] = $data['firstBloodAssist'];
            $gold[] = $data['goldEarned'];
            $longestTimeSpentLiving[] = $data['longestTimeSpentLiving'];
            $visionScore[] = $data['visionScore'];
            $timeCCingOthers[] = $data['timeCCingOthers'];
        }

        $avgKills = $this->getAvg($kills);
        $avgDeaths = $this->getAvg($deaths);
        $avgAssists = $this->getAvg($assists);
        if (isset($fb)) {
            $avgFb = $this->getAvg($fb);
            $arr['avgFb'] = $avgFb;
        }
        if (isset($fba)) {
            $avgFba = $this->getAvg($fba);
            $arr['avgFba'] = $avgFba;
        }
        if (isset($longestTimeSpentLiving)) {
            $avgAlive = $this->getAvg($longestTimeSpentLiving);
            $arr['avgAlive'] = $avgAlive;
        }
        if (isset($visionScore)) {
            $avgVisionScore = $this->getAvg($visionScore);
            $arr['avgVisionScore'] = $avgVisionScore;
        }
        if (isset($timeCCingOthers)) {
            $avgTimeCc = $this->getAvg($timeCCingOthers);
            $arr['avgTimeCc'] = $avgTimeCc;
        }
        if (isset($gold)) {
            $avgGold = $this->getAvg($gold);
            $arr['avgGold'] = $avgGold;
        }


        $arr['avgKill'] = $avgKills;
        $arr['avgDeaths'] = $avgDeaths;
        $arr['avgAssists'] = $avgAssists;

        return $arr;
//        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($arr, 'json'), Response::HTTP_OK);
    }

    private function getAvg($data)
    {
        if (count($data) > 1) {
            return array_sum($data) / count($data);
        }
    }

    private function getParticipantId($arrParticipants, $summonerName)
    {
        foreach ($arrParticipants as $participant) {
            if ($participant['player']['summonerName'] == $summonerName) {
                return $participant['participantId'];
            }
        }
    }
}
