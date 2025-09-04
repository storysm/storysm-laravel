<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Story;
use Illuminate\Support\Facades\Log;

class StoryObserver
{
    public function saved(Story $story): void
    {
        // Only run the sync logic if the content field has been changed.
        if ($story->isDirty('content')) {
            $languageCodesInContent = collect($story->getTranslations('content'))
                ->filter(fn ($value) => ! empty($value)) // Ensure value is not null or empty string
                ->keys();

            $existingLanguages = Language::whereIn('code', $languageCodesInContent)->pluck('code', 'id');
            $languageIdsToSync = $existingLanguages->keys();

            // Log a warning for any language codes that are in the content but not in the languages table.
            $unknownCodes = $languageCodesInContent->diff($existingLanguages->values());
            if ($unknownCodes->isNotEmpty()) {
                Log::warning('Attempted to sync story with unknown language codes.', [
                    'story_id' => $story->id,
                    'unknown_codes' => $unknownCodes->all(),
                ]);
            }

            // Automatically attaches new, detaches missing, and leaves existing relations.
            $story->languages()->sync($languageIdsToSync);
        }
    }
}
