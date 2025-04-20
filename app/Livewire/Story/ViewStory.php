<?php

namespace App\Livewire\Story;

use App\Models\Story; // Import the Story model
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class ViewStory extends Component
{
    public Story $story;

    public function mount(Story $story): void
    {
        $description = Str::limit(strip_tags($story->content), 160);

        SEOTools::setTitle($story->title);
        SEOTools::setDescription($description);
        SEOTools::opengraph()->setTitle($story->title);
        SEOTools::opengraph()->setDescription($description);
        SEOTools::twitter()->setTitle($story->title);
        SEOTools::twitter()->setDescription($description);
        SEOTools::jsonLd()->setTitle($story->title);
        SEOTools::jsonLd()->setDescription($description);
        SEOTools::jsonLd()->setType('Article');

        $coverImageUrl = $story->coverMedia?->url;
        if ($coverImageUrl) {
            SEOTools::opengraph()->addImage($coverImageUrl);
            SEOTools::twitter()->addImage($coverImageUrl);
            SEOTools::jsonLd()->addImage($coverImageUrl);
        }

        $this->story = $story;
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            route('home') => __('navigation-menu.menu.home'),
            route('stories.index') => trans_choice('story.resource.model_label', 2),
            0 => Str::limit($this->story->title, 50),
        ];

        return $breadcrumbs;
    }

    public function render(): View
    {
        return view('livewire.story.view-story');
    }
}
