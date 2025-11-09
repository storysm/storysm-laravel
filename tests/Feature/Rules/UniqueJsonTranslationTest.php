<?php

namespace Tests\Feature\Rules;

use App\Models\Page;
use App\Rules\UniqueJsonTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase; // Using the existing Page model for testing

class UniqueJsonTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validates_unique_json_translation_correctly(): void
    {
        // Create a page with a unique title in 'en' locale
        Page::factory()->create([
            'title' => ['en' => 'Unique Title', 'es' => 'Título Único'],
            'content' => 'Some content',
        ]);

        // Test case 1: Valid - title is unique in 'en'
        $validator = Validator::make([
            'title' => 'Another Unique Title',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'en')],
        ]);
        $this->assertTrue($validator->passes());

        // Test case 2: Invalid - title is not unique in 'en'
        $validator = Validator::make([
            'title' => 'Unique Title',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'en')],
        ]);
        $this->assertFalse($validator->passes());
        $this->assertEquals(__('rule.unique_json_translation', ['attribute' => 'title']), $validator->errors()->first('title'));

        // Test case 3: Valid - title is unique in 'es'
        $validator = Validator::make([
            'title' => 'Another Unique Title ES',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'es')],
        ]);
        $this->assertTrue($validator->passes());

        // Test case 4: Invalid - title is not unique in 'es'
        $validator = Validator::make([
            'title' => 'Título Único',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'es')],
        ]);
        $this->assertFalse($validator->passes());
        $this->assertEquals(__('rule.unique_json_translation', ['attribute' => 'title']), $validator->errors()->first('title'));
    }

    public function test_validates_unique_json_translation_correctly_with_ignore_id(): void
    {
        // Create a page
        $page1 = Page::factory()->create([
            'title' => ['en' => 'Existing Title', 'es' => 'Título Existente'],
            'content' => 'Content 1',
        ]);

        // Test case 1: Valid - updating the same page with the same title should pass
        $validator = Validator::make([
            'title' => 'Existing Title',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'en', $page1->id)],
        ]);
        $this->assertTrue($validator->passes());

        // Create another page with a different title
        $page2 = Page::factory()->create([
            'title' => ['en' => 'Another Title', 'es' => 'Otro Título'],
            'content' => 'Content 2',
        ]);

        // Test case 2: Invalid - trying to use page1's title for page2 (ignoring page2's ID)
        $validator = Validator::make([
            'title' => 'Existing Title',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'en', $page2->id)],
        ]);
        $this->assertFalse($validator->passes());
        $this->assertEquals(__('rule.unique_json_translation', ['attribute' => 'title']), $validator->errors()->first('title'));

        // Test case 3: Valid - updating page1 with a new unique title
        $validator = Validator::make([
            'title' => 'Updated Unique Title',
        ], [
            'title' => [new UniqueJsonTranslation('pages', 'title', 'en', $page1->id)],
        ]);
        $this->assertTrue($validator->passes());
    }
}
