<?php

namespace App\Modules\NamedLocations;

use App\Graph\GraphClient;

class NamedLocationsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch all named locations (IP ranges + country locations).
     */
    public function getAll(): array
    {
        try {
            $data = $this->graph->get(
                '/identity/conditionalAccess/namedLocations',
                ['$top' => '200'],
                'named_locations',
                900
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('NamedLocations getAll: ' . $e->getMessage());
            return [];
        }
    }

    /** Returns the last Graph error, or null. */
    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }

    /**
     * Split locations into IP-based and country-based.
     */
    public function classify(array $locations): array
    {
        $ip      = [];
        $country = [];

        foreach ($locations as $loc) {
            $type = $loc['@odata.type'] ?? '';
            if (str_contains($type, 'ipNamedLocation')) {
                $ip[] = $loc;
            } elseif (str_contains($type, 'countryNamedLocation')) {
                $country[] = $loc;
            }
        }

        return ['ip' => $ip, 'country' => $country];
    }
}
