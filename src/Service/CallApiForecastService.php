<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiForecastService
{

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getForecastForNancy(): array {

        $response = $this->client->request(
            'GET',
            'https://api.meteo-concept.com/api/forecast/daily?insee=54395&token=9b39f93d43dc60f8a1f6936dde05973e5a6366fb70e4cd6230d7bd20a1a0d792'
        );

        return $response->toArray();
    }

}