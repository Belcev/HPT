<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi;

use GuzzleHttp\Client;

class NominatimGeocoder implements GeocoderInterface
{
    private const string BASE_URL = 'https://nominatim.openstreetmap.org/search';

    public function __construct(
        private readonly Client $client
    ) {
    }

    public function geocode(string $address): ?string
    {
        try {
            $response = $this->client->get(self::BASE_URL, [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'MiniEshopAPI/1.0',
                ],
                'timeout' => 5,
            ]);

            /** @var array<int, array{lat: string, lon: string}> $results */
            $results = json_decode((string) $response->getBody(), true);

            if (! isset($results[0]['lat'], $results[0]['lon'])) {
                return null;
            }

            return "{$results[0]['lat']},{$results[0]['lon']}";
        } catch (\Throwable) {
            return null;
        }
    }
}
