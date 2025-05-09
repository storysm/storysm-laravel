<?php

use App\Livewire\Comment\ViewComment;
use Illuminate\Support\Facades\Route;

Route::get('/comments/{comment}', ViewComment::class)
    ->name('comments.show');
