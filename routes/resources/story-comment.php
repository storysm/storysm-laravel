<?php

use App\Livewire\StoryComment\ViewStoryComment;
use Illuminate\Support\Facades\Route;

Route::get('/story-comments/{storyComment}', ViewStoryComment::class)
    ->name('story-comments.show');
