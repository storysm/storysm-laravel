<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Locale;

class StoryObserver
{
    /**
     * Handle the Story "saving" event.
     */
    public function saving(Story $story): void
    {
        $this->calculateEffectiveAgeRating($story);
    }

    /**
     * Calculate the effective age rating for the story.
     */
    protected function calculateEffectiveAgeRating(Story $story): void
    {
        // Load age ratings if not already loaded, or if the relationship might have changed
        // This ensures we have the latest ratings, especially if they were just synced.
        $story->loadMissing('ageRatings');

        /** @var ?int */
        $maxAgeRepresentation = $story->ageRatings->max('age_representation');

        // Set the effective value. If no ratings, max() returns null.
        $story->age_rating_effective_value = $maxAgeRepresentation;
    }

    public function saved(Story $story): void
    {
        // Only run the sync logic if the content field has been changed.
        if ($story->isDirty('content')) {
            $languageCodesInContent = collect($story->getTranslations('content'))
                ->filter(fn ($value) => ! empty($value)) // Ensure value is not null or empty string
                ->keys();

            $existingLanguages = Language::whereIn('code', $languageCodesInContent)->pluck('code', 'id');
            $languageIdsToSync = $existingLanguages->keys();

            $unknownCodes = $languageCodesInContent->diff($existingLanguages->values());
            if ($unknownCodes->isNotEmpty()) {
                Log::warning('Attempted to sync story with unknown language codes. Creating new language entries.', [
                    'story_id' => $story->id,
                    'unknown_codes' => $unknownCodes->all(),
                ]);

                foreach ($unknownCodes as $code) {
                    Language::firstOrCreate(['code' => $code], ['name' => Locale::getDisplayLanguage($code, 'en')]);
                }

                // Re-fetch existing languages to include newly created ones
                $existingLanguages = Language::whereIn('code', $languageCodesInContent)->pluck('code', 'id');
                $languageIdsToSync = $existingLanguages->keys();
            }

            // Automatically attaches new, detaches missing, and leaves existing relations.
            $story->languages()->sync($languageIdsToSync);
        }
    }
}
