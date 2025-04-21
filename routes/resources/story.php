<?php

use App\Livewire\Story\ListStories;
use App\Livewire\Story\ViewStory;
use Illuminate\Support\Facades\Route;

Route::get('/stories', ListStories::class)
    ->name('stories.index');
Route::get('/stories/{story}', ViewStory::class)
    ->name('stories.show');
