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
     * Create a country-based Named Location.
     *
     * @param string[] $countries ISO 3166-1 alpha-2 country codes
     */
    public function createCountryLocation(string $name, array $countries, bool $includeUnknown = false): array
    {
        $this->graph->getCache()->forget('named_locations');
        return $this->graph->post('/identity/conditionalAccess/namedLocations', [
            '@odata.type'                       => '#microsoft.graph.countryNamedLocation',
            'displayName'                       => $name,
            'countriesAndRegions'               => array_values($countries),
            'includeUnknownCountriesAndRegions' => $includeUnknown,
        ]);
    }

    /**
     * Create an IP-based Named Location.
     *
     * @param string[] $cidrs CIDR addresses, e.g. ['10.0.0.0/8', '192.168.1.0/24']
     */
    public function createIpLocation(string $name, array $cidrs, bool $trusted = false): array
    {
        $ranges = [];
        foreach ($cidrs as $cidr) {
            $cidr = trim($cidr);
            if ($cidr === '') continue;
            $type = str_contains($cidr, ':') ? '#microsoft.graph.iPv6CidrRange' : '#microsoft.graph.iPv4CidrRange';
            $ranges[] = ['@odata.type' => $type, 'cidrAddress' => $cidr];
        }
        $this->graph->getCache()->forget('named_locations');
        return $this->graph->post('/identity/conditionalAccess/namedLocations', [
            '@odata.type' => '#microsoft.graph.ipNamedLocation',
            'displayName' => $name,
            'isTrusted'   => $trusted,
            'ipRanges'    => $ranges,
        ]);
    }

    /**
     * Delete a Named Location by ID.
     */
    public function delete(string $id): void
    {
        $this->graph->delete('/identity/conditionalAccess/namedLocations/' . $id);
        $this->graph->getCache()->forget('named_locations');
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
