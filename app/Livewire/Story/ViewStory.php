<?php

namespace App\Livewire\Story;

use App\Facades\AgeVerification;
use App\Filament\Resources\StoryResource;
use App\Models\Story;
use App\Scopes\StoryFilterScope;
use Artesaos\SEOTools\Facades\SEOTools;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewStory extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Story $story;

    public bool $isAgeVerified = false;

    public function mount(Story $story): void
    {
        // Load story without the StoryFilterScope to prevent 404s on restricted stories
        $this->story = Story::withoutGlobalScope(StoryFilterScope::class)
            ->where('id', $story->id)
            ->firstOrFail();

        // Check if user is authorized to view the story
        try {
            $this->authorize('viewPublic', $this->story);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(404);
        }

        $ageLimit = config('age_rating.limit_years', 16);
        $storyRating = $this->story->age_rating_effective_value;

        // Treat null ratings as requiring age verification
        if ($storyRating === null || $storyRating >= $ageLimit) {
            if (! AgeVerification::hasAgeSet()) {
                $this->isAgeVerified = false;
            } else {
                $userAge = AgeVerification::getAge();
                $storyAgeRating = $storyRating ?? 18; // Default to high value if null

                if ($userAge < $storyAgeRating) {
                    redirect()->route('content.forbidden');

                    return;
                }

                $this->isAgeVerified = true;
            }
        } else {
            $this->isAgeVerified = true;
        }

        // Set up SEO metadata
        $description = Str::limit(strip_tags($this->story->content), 160);

        SEOTools::setTitle($this->story->title);
        SEOTools::setDescription($description);
        SEOTools::opengraph()->setTitle($this->story->title);
        SEOTools::opengraph()->setDescription($description);
        SEOTools::twitter()->setTitle($this->story->title);
        SEOTools::twitter()->setDescription($description);
        SEOTools::jsonLd()->setTitle($this->story->title);
        SEOTools::jsonLd()->setDescription($description);
        SEOTools::jsonLd()->setType('Article');

        $coverImageUrl = $this->story->coverMedia?->url;
        if ($coverImageUrl) {
            SEOTools::opengraph()->addImage($coverImageUrl);
            SEOTools::twitter()->addImage($coverImageUrl);
            SEOTools::jsonLd()->addImage($coverImageUrl);
        }

        // Increment view count if already age verified and allowed
        if ($this->isAgeVerified) {
            $this->incrementViewCount();
        }
    }

    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        $actions = [];
        $actions[] = Action::make('edit')
            ->authorize(StoryResource::canEdit($this->story))
            ->label(__('story.action.edit'))
            ->url(route('filament.admin.resources.stories.edit', $this->story));

        return $actions;
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

    #[On('storyCommentCreated')]
    public function refreshStory(): void
    {
        $this->story->refresh();
    }

    #[On('ageVerified')]
    public function handleAgeVerified(): void
    {
        $this->isAgeVerified = true;

        // Increment view count when age is verified
        $this->incrementViewCount();
    }

    protected function incrementViewCount(): void
    {
        if (Gate::allows('incrementViewCount', $this->story)) {
            $this->story->incrementViewCount();
        }
    }

    public function render(): View
    {
        return view('livewire.story.view-story');
    }
}
