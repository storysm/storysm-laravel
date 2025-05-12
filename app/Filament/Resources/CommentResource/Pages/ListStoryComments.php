<?php

namespace App\Filament\Resources\CommentResource\Pages;

use App\Filament\Resources\StoryCommentResource;
use Filament\Resources\Pages\ListRecords;

class ListStoryComments extends ListRecords
{
    protected static string $resource = StoryCommentResource::class;
}
