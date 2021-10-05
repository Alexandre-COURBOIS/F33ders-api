<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RiotApiService
{

    private HttpClientInterface $httpClient;

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
            'http://ddragon.leagueoflegends.com/cdn/9.3.1/data/en_US/champion.json'
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

    public function getOneChampionById($id)
    {
        $response = $this->httpClient->request(
            'GET',
            'http://ddragon.leagueoflegends.com/cdn/9.3.1/data/en_US/champion.json'
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
}