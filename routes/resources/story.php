<?php

use App\Livewire\Story\ListStories;
use Illuminate\Support\Facades\Route;

Route::get('/stories', ListStories::class)
    ->name('stories.index');
