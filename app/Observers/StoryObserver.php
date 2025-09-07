<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Locale;

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
