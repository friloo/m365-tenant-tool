<?php

namespace App\Modules\LicenseCosts;

use App\Auth\LocalAuth;
use App\Core\View;
use App\Modules\LicenseAdvisor\LicenseAdvisorService;

class LicenseCostsController
{
    public function index(): void
    {
        LocalAuth::require();

        $priceMode = ($_GET['price_mode'] ?? 'npo') === 'standard' ? 'standard' : 'npo';
        $priceKey  = $priceMode === 'standard' ? 'price_eur' : 'price_npo_eur';

        /** @var LicenseAdvisorService $advisor */
        $advisor = app_service(LicenseAdvisorService::class);
        $skus    = $advisor->getSkusWithCriteria();

        $rows       = [];
        $totalMonth = 0.0;
        $wasteMonth = 0.0;

        foreach ($skus as $sku) {
            $price = $sku[$priceKey] ?? null;
            if ($price === null) {
                // Try the other key as fallback label
                $price = null;
            }
            $consumed  = (int)$sku['consumed'];
            $total     = (int)$sku['total'];
            $available = (int)$sku['available'];

            $monthlyCost  = $price !== null ? round($price * $consumed, 2) : null;
            $wastedCost   = $price !== null ? round($price * $available, 2) : null;
            $annualCost   = $monthlyCost !== null ? round($monthlyCost * 12, 2) : null;

            if ($monthlyCost !== null) {
                $totalMonth += $monthlyCost;
                $wasteMonth += $wastedCost ?? 0;
            }

            $rows[] = [
                'name'        => $sku['name'],
                'partNumber'  => $sku['partNumber'],
                'consumed'    => $consumed,
                'total'       => $total,
                'available'   => $available,
                'pct'         => $sku['pct'],
                'price'       => $price,
                'monthlyCost' => $monthlyCost,
                'wastedCost'  => $wastedCost,
                'annualCost'  => $annualCost,
                'tier'        => $sku['tier'],
            ];
        }

        // Sort by monthly cost desc
        usort($rows, fn($a, $b) => ($b['monthlyCost'] ?? -1) <=> ($a['monthlyCost'] ?? -1));

        View::render('licensecosts/index', [
            'pageTitle'   => 'Lizenzkosten',
            'rows'        => $rows,
            'totalMonth'  => round($totalMonth, 2),
            'wasteMonth'  => round($wasteMonth, 2),
            'totalAnnual' => round($totalMonth * 12, 2),
            'priceMode'   => $priceMode,
        ]);
    }
}
