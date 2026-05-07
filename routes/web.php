<?php

use App\Livewire\PortalAbout;
use App\Livewire\PortalApps;
use App\Livewire\PortalHome;
use Illuminate\Support\Facades\Route;

Route::get('/', PortalHome::class);
Route::get('/apps', PortalApps::class);
Route::get('/about', PortalAbout::class);
