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
        // sensitivityLabels lives in the BETA endpoint (v1.0 has no such path —
        // a v1.0 call 404s and is swallowed by GraphClient, so the previous
        // v1.0-first order returned empty and never reached the fallback).
        try {
            $data = $this->graph->get(
                'https://graph.microsoft.com/beta/security/informationProtection/sensitivityLabels',
                ['$top' => '200'],
                'sensitivity_labels',
                1800
            );
            if ($this->graph->getLastError() === null && !empty($data['value'])) {
                return $data['value'];
            }
        } catch (\Throwable $e) {
            error_log('SensitivityLabels getLabels (beta): ' . $e->getMessage());
        }

        // Legacy fallback (older information-protection labels endpoint).
        try {
            $data = $this->graph->get(
                '/informationProtection/policy/labels',
                [],
                'sensitivity_labels_legacy',
                1800
            );
            return $data['value'] ?? [];
        } catch (\Throwable $e) {
            error_log('SensitivityLabels getLabels (legacy): ' . $e->getMessage());
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
