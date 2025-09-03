<?php

namespace App\Enums\Vote;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Type: string implements HasColor, HasIcon, HasLabel
{
    case Up = 'up';
    case Down = 'down';

    public function getColor(): string
    {
        return match ($this) {
            self::Up => 'primary',
            self::Down => 'danger',
        };
    }

    public function getIcon(): string
    {
        return 'heroicon-m-hand-thumb-'.$this->value;
    }

    public function getLabel(): string
    {
        return __('story-vote.type.'.$this->value);
    }
}
