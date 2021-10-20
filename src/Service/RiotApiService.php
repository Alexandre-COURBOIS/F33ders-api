<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RiotApiService
{

    private HttpClientInterface $httpClient;

    private string $urlDataDragon = 'http://ddragon.leagueoflegends.com';
    private string $getAllChampions = '/cdn/11.19.1/data/fr_FR/champion.json';
    private string $getAllitems = '/cdn/11.19.1/data/fr_FR/item.json';

    public function __construct(HttpClientInterface $client)
    {
        $this->httpClient = $client;
    }

    public function getApi()
    {
        $response = $this->httpClient->request(
            'GET',
            'https://51.255.160.47:8181'
        );

        $content = $response->getContent();

        $content = $response->toArray();

        return $content;
    }

    public function getUserApi($username)
    {
        $response = $this->httpClient->request(
            'GET',
            'http://51.255.160.47:8181/euw1/passerelle/getHistoryMatchList/' . $username
        );

        $content = $response->getContent();

        $content = $response->toArray();

        return $content;
    }

    public function getGameByid($id)
    {
        $response = $this->httpClient->request(
            'GET',
            'http://51.255.160.47:8181/euw1/passerelle/getHistoryMatch/' . $id
        );

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    public function getChampionsById($championsid)
    {
        $response = $this->httpClient->request(
            'GET',
            'http://ddragon.leagueoflegends.com/cdn/11.19.1/data/fr_FR/champion.json'
        );

        $content = $response->toArray();
        $champions = [];
        for ($i = 0; $i < count($championsid); $i++) {
            foreach ($content['data'] as $key => $value) {
                if (in_array($championsid[$i], $value)) {
                    array_push($champions, $value);
                    break;
                }
                if ($key === array_key_last($content['data'])) {
                    array_push($champions, ['name' => "Champion inconnu", 'title' => '']);
                }
            }
        }
        return $champions;
    }

    public function getChampions(): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->urlDataDragon . $this->getAllChampions
        );

        $content = $response->getContent();

        $content = $response->toArray();

        return $content;
    }

    public function getItems(): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->urlDataDragon . $this->getAllitems
        );

        $content = $response->getContent();

        $content = $response->toArray();

        return $content;
    }

    public function getOneChampionById($id)
    {
        $response = $this->httpClient->request(
            'GET',
            'http://ddragon.leagueoflegends.com/cdn/11.19.1/data/fr_FR/champion.json'
        );

        $content = $response->toArray();

        foreach ($content['data'] as $key => $value) {
            if (in_array($id, $value)) {
                return $value;
            }
        }
    }

    public function avgChampionsPlayed($array, $round = 1)
    {
        $num = count($array);

        return array_map(
            function ($val) use ($num, $round) {
                return array('count' => $val, 'avg' => round($val / $num * 100, $round));
            },
            array_count_values($array)
        );
    }

    public function getStats($foreachEntry, $param = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null): array
    {

        $outPutTable = [];

        foreach ($foreachEntry as $output) {
            $outPutTable[$param2][] = $output[$param][$param2];
            $outPutTable[$param3][] = $output[$param][$param3];
            $outPutTable[$param4][] = $output[$param][$param4];
            $outPutTable[$param5][] = $output[$param][$param5];
        }

        $totalAttack = $outPutTable[$param2][0] + $outPutTable[$param2][1] + $outPutTable[$param2][2] + $outPutTable[$param2][3] + $outPutTable[$param2][4];
        $totalDefense = $outPutTable[$param3][0] + $outPutTable[$param3][1] + $outPutTable[$param3][2] + $outPutTable[$param3][3] + $outPutTable[$param3][4];
        $totalMagic = $outPutTable[$param4][0] + $outPutTable[$param4][1] + $outPutTable[$param4][2] + $outPutTable[$param4][3] + $outPutTable[$param4][4];
        $totalDifficulty = $outPutTable[$param5][0] + $outPutTable[$param5][1] + $outPutTable[$param5][2] + $outPutTable[$param5][3] + $outPutTable[$param5][4];

        $outPutTable[$param2][] = ['totalAttack' => $totalAttack];
        $outPutTable[$param3][] = ['totalDefense' => $totalDefense];
        $outPutTable[$param4][] = ['totalMagic' => $totalMagic];
        $outPutTable[$param5][] = ['totalDifficulty' => $totalDifficulty];


        return $outPutTable;
    }
}