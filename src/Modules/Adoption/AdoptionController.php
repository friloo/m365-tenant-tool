<?php

namespace App\Modules\Adoption;

use App\Auth\LocalAuth;
use App\Core\Redirect;

class AdoptionController
{
    /**
     * The adoption dashboard was merged into the unified "Nutzung & Adoption"
     * page (/usagereports) as a tab. Kept as a redirect for old links/bookmarks.
     */
    public function index(): void
    {
        LocalAuth::require();
        Redirect::to('/usagereports?tab=adoption');
    }
}
