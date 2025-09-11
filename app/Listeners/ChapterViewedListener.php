<?php

namespace App\Listeners;

use App\Services\ChapterAnalysis\ChapterAnalysisService;

class ChapterViewedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $service = new ChapterAnalysisService;

        $service->storeRecord($event->subMogou);
    }
}
