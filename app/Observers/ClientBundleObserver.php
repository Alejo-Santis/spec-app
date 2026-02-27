<?php

namespace App\Observers;

use App\Models\ClientBundle;

class ClientBundleObserver
{
    public function saving(ClientBundle $clientBundle): void
    {
        if ($clientBundle->quantity_remaining <= 0) {
            $clientBundle->is_active = false;
        }
    }
}
