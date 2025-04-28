<?php

namespace App\Observers;

use App\Models\Niche;

class NicheObserver
{
    /**
     * Handle the Niche "created" event.
     */
    public function created(Niche $niche): void
    {
        //
    }

    /**
     * Handle the Niche "updated" event.
     */
    public function updated(Niche $niche): void
    {
        //
    }

    /**
     * Handle the Niche "deleted" event.
     */
    public function deleted(Niche $niche): void
    {
        //
    }

    /**
     * Handle the Niche "restored" event.
     */
    public function restored(Niche $niche): void
    {
        //
    }

    /**
     * Handle the Niche "force deleted" event.
     */
    public function forceDeleted(Niche $niche): void
    {
        //
    }
}
