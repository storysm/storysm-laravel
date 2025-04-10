<?php

namespace App\Enums\Story;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Publish = 'publish';

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            default => 'success',
        };
    }

    public function getLabel(): string
    {
        return __('story.resource.status.'.$this->value);
    }
}
