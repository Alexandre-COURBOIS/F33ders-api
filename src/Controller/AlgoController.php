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

    public function findTeam($data)
    {
        $arrayLane = [
          "top" => '',
          "jungle" => '',
          "mid" => '',
          'adc' => '',
          'support' => ''  
        ];
        $allPLayer = $this->playerService->getAllPlayer();
        if($data['mainRole'] == 'ADC') {
            //findSupport
            foreach($allPLayer as $player) {
                $searchPlayer = $this->process($player->getUsername());
                if($searchPlayer['mainRole'] == "SUPPORT" || $searchPlayer['secondaryRole'] == "SUPPORT") {
                    if($searchPlayer['agressivity'] == $data['agressivty']) {
                        $arrayLane['support'] = $searchPlayer;
                    }
                }
            }
        } elseif($data['mainRole'] == 'SUPPORT') {
            foreach($allPLayer as $player) {
                $searchPlayer = $this->process($player->getUsername());
                if($searchPlayer['mainRole'] == "ADC" || $searchPlayer['secondaryRole'] == "ADC") {
                    if($searchPlayer['agressivity'] == $data['agressivty']) {
                        $arrayLane['support'] = $searchPlayer;
                    }
                }
            }
        } elseif($data['mainRole'] == 'JUNGLE') {
            //find mid
        } elseif($data['mainRole'] == 'TOP') {
            // fill
        } elseif($data['mainRole'] == 'MID') {
            //find jgl
        }
    }
    
    /**
     * @Route("api/algo/process", name="algo_getavgstats")
     */
    public function process($username): array
    {
//        $datas = json_decode($request->getContent(), true);
        
//        dump($this->playerService->getAllPlayer());
        
        if(!empty($username)) {
            $data = $this->getAvgStats($username);
            $role = $this->getRole($username);

            if(!empty($role)) {
                $player['mainRole'] = $role['main'];
                $player['secondaryRole'] = $role['secondary'];
            }

            if(($data['avgFb'] + $data['avgFba']) > 0.2) {
                $player['agressivity'] = true;
            } else {
                $player['agressivity'] = false;
            }

            if($data['avgDeaths'] > 3) {
                $player['vulnerable'] = true;
            } else {
                $player['vulnerable'] = false;
            }
            if(($data['avgKill'] + $data['avgAssists']) > 15) {
                $player['teamfighter'] = true;
            } else {
                $player['teamfighter'] = false;
            }
            if($data['avgTimeCc'] > 20) {
                $player['ccer'] = true;
            } else {
                $player['ccer'] = false;
            }
            if($data['avgGold'] > 10000) {
                $player['moneyPlayer'] = true;
            } else {
                $player['moneyPlayer'] = false;
            }

            return $player;   
        }
        
    }
    
    public function getRole($username): array
    {
        $historique = $this->riotApiService->getUserApi($username);
        
//        dump($historique);
//        dump($this->playerService->matchPlayedWithChampions('Druxys'));
        
        $tabRoles = [];
        foreach($historique['matches'] as $match) {
            if($match['role'] == "DUO_SUPPORT") {
                $tabRoles[] = "SUPPORT";
            } elseif($match['role'] == "DUO_CARRY") {
                $tabRoles[] = "ADC";
            } elseif($match['lane'] !== "BOTTOM" && $match['lane'] !== "NONE") {
                $tabRoles[] = $match['lane'];
            }
        }
        
        $occurences = array_count_values($tabRoles);
        
        $mainRoles = [];
        foreach($occurences as $key => $value) {
            if($value == max($occurences)) {
                if(!isset($mainRoles['main'])) {
                    $mainRoles['main'] = $key;
                } else {
                    $mainRoles['secondary'] = $key;
                }
                unset($occurences[$key]);
            }
            if(count($mainRoles) == 2) {
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
//        dump($allMatchPlayer);
        $games = [];
        foreach($allMatchPlayer->getUserHistory()[0]['matches'] as $match)
        {
//            dump($match);
            $games[] = $this->dm->getRepository(Match::class)->findOneBy(['matchId' => $match['gameId']]);
        }
        
//        dump($games[0]);
        $userData = [];
        $i = 0;
        foreach($games as $match) {
            $idParticipant = $this->getParticipantId($match->getMatch()[12], 'Druxys');
            
            foreach($match->getMatch()[11] as $userGameInfo) {
                if($userGameInfo['participantId'] == $idParticipant) {
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
//        dump($userData);
        
        $kills = [];
        $deaths = [];
        $assists = [];
        foreach($userData as $data) {
            $kills[] = $data['kills'];
            $deaths[] = $data['deaths'];
            $assists[] = $data['assists'];
            $fb[] = $data['firstBloodKill'];
            $fba[]= $data['firstBloodAssist'];
            $gold[] = $data['goldEarned'];
            $longestTimeSpentLiving[] = $data['longestTimeSpentLiving'];
            $visionScore[] = $data['visionScore'];
            $timeCCingOthers[] = $data['timeCCingOthers'];
        }
        
        $avgKills = $this->getAvg($kills);
        $avgDeaths = $this->getAvg($deaths);
        $avgAssists = $this->getAvg($assists);
        $avgFb = $this->getAvg($fb);
        $avgFba = $this->getAvg($fba);
        $avgAlive = $this->getAvg($longestTimeSpentLiving);
        $avgVisionScore = $this->getAvg($visionScore);
        $avgTimeCc = $this->getAvg($timeCCingOthers);
        $avgGold = $this->getAvg($gold);
        
        $arr['avgKill'] = $avgKills;
        $arr['avgDeaths'] = $avgDeaths;
        $arr['avgAssists'] = $avgAssists;
        $arr['avgFb'] = $avgFb;
        $arr['avgFba'] = $avgFba;
        $arr['avgAlive'] = $avgAlive;
        $arr['avgVisionScore'] = $avgVisionScore;
        $arr['avgTimeCc'] = $avgTimeCc;
        $arr['avgGold'] = $avgGold;
        
        return $arr;
//        return JsonResponse::fromJsonString($this->serializerService->SimpleSerializer($arr, 'json'), Response::HTTP_OK);
    }
    
    private function getAvg($data)
    {
        return array_sum($data)/count($data);
    }
    
    private function getParticipantId($arrParticipants, $summonerName) {
        foreach($arrParticipants as $participant) {
            if($participant['player']['summonerName'] == $summonerName) {
                return $participant['participantId'];
            }
        }
    }
}
