<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:ping', function (): void {
    $this->comment('EcoTrack SaaS is alive.');
})->purpose('Sanity check for the application.');
