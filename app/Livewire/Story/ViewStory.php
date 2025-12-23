<?php

namespace App\Livewire\Story;

use App\Facades\AgeVerification;
use App\Filament\Resources\StoryResource;
use App\Models\Story;
use App\Scopes\GuestStoryFilterScope;
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

    public ?int $dob_day = null;

    public ?int $dob_month = null;

    public ?int $dob_year = null;

    public bool $remember_me = false;

    public bool $isAgeVerified = false;

    public function mount(Story $story): void
    {
        // Load story without the GuestStoryFilterScope to prevent 404s on restricted stories
        $this->story = Story::withoutGlobalScope(GuestStoryFilterScope::class)
            ->where('id', $story->id)
            ->firstOrFail();

        // Check if user is authorized to view the story
        try {
            $this->authorize('viewPublic', $this->story);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(404);
        }

        // Check if age is set in the service
        if (! AgeVerification::hasAgeSet()) {
            // Age not set, show age gate
            $this->isAgeVerified = false;
        } else {
            // Age is set, check if user is old enough
            $userAge = AgeVerification::getAge();
            $storyAgeRating = $this->story->age_rating_effective_value;

            if ($userAge < $storyAgeRating) {
                // User is too young, redirect to forbidden page
                redirect()->route('content.forbidden');

                return;
            } else {
                // User is old enough, allow access
                $this->isAgeVerified = true;
            }
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

        // Increment view count if allowed
        if (Gate::allows('incrementViewCount', $this->story)) {
            $this->story->incrementViewCount();
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

    /**
     * Verify the user's age and handle age gate bypass
     */
    public function verifyAge(): void
    {
        $this->validate([
            'dob_day' => ['required', 'integer', 'min:1', 'max:31'],
            'dob_month' => ['required', 'integer', 'min:1', 'max:12'],
            'dob_year' => ['required', 'integer', 'min:1900', 'max:'.date('Y')],
            'remember_me' => ['boolean'],
        ]);

        // Validate that the date is valid
        $dateString = sprintf('%04d-%02d-%02d', $this->dob_year, $this->dob_month, $this->dob_day);

        try {
            $date = new \DateTime($dateString);
            $now = new \DateTime;

            if ($date > $now) {
                $this->addError('dob_year', 'Date of birth cannot be in the future.');

                return;
            }
        } catch (\Exception $e) {
            $this->addError('dob_day', 'Invalid date provided.');

            return;
        }

        // Calculate age using the service
        $age = AgeVerification::calculateAge($dateString);

        // Store the age using the service
        AgeVerification::setAge($age, $this->remember_me);

        // Re-run the age comparison check
        $storyAgeRating = $this->story->age_rating_effective_value;

        if ($age < $storyAgeRating) {
            // User is too young, redirect to forbidden page
            redirect()->route('content.forbidden');

            return;
        } else {
            // User is old enough, allow access
            $this->isAgeVerified = true;
        }
    }

    public function render(): View
    {
        return view('livewire.story.view-story');
    }
}
