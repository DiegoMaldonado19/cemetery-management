<?php

namespace App\Observers;

use App\Models\Exhumation;

class ExhumationObserver
{
    /**
     * Handle the Exhumation "created" event.
     */
    public function created(Exhumation $exhumation): void
    {
        //
    }

    /**
     * Handle the Exhumation "updated" event.
     */
    public function updated(Exhumation $exhumation): void
    {
        //
    }

    /**
     * Handle the Exhumation "deleted" event.
     */
    public function deleted(Exhumation $exhumation): void
    {
        //
    }

    /**
     * Handle the Exhumation "restored" event.
     */
    public function restored(Exhumation $exhumation): void
    {
        //
    }

    /**
     * Handle the Exhumation "force deleted" event.
     */
    public function forceDeleted(Exhumation $exhumation): void
    {
        //
    }
}
