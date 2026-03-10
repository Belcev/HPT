<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi;

interface GeocoderInterface
{
    public function geocode(string $address): ?string;
}
