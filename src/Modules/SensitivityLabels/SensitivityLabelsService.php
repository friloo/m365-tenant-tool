<?php

namespace App\Modules\SensitivityLabels;

use App\Graph\GraphClient;

class SensitivityLabelsService
{
    public function __construct(private GraphClient $graph) {}

    /**
     * Fetch all sensitivity labels from the tenant's Information Protection policy.
     * Requires InformationProtectionPolicy.Read.All application permission.
     */
    public function getLabels(): array
    {
        try {
            $data = $this->graph->get(
                '/security/informationProtection/sensitivityLabels',
                ['$top' => '200'],
                'sensitivity_labels',
                1800
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('SensitivityLabels getLabels (v1): ' . $e->getMessage());
        }

        // Fallback: try the beta endpoint pattern
        try {
            $data = $this->graph->get(
                '/informationProtection/policy/labels',
                [],
                'sensitivity_labels_v2',
                1800
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('SensitivityLabels getLabels (v2): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch data classification settings / policy.
     */
    public function getPolicySettings(): array
    {
        try {
            $data = $this->graph->get(
                '/security/informationProtection/labelPolicySettings',
                [],
                'sensitivity_policy_settings',
                1800
            );
            return $data ?? [];
        } catch (\Throwable $e) {
            error_log('SensitivityLabels getPolicySettings: ' . $e->getMessage());
            return [];
        }
    }

    public function getLastError(): ?array
    {
        return $this->graph->getLastError();
    }
}
