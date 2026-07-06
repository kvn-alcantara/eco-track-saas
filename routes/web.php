<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'app' => 'EcoTrack SaaS',
    'status' => 'ok',
]));
