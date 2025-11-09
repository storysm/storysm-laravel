<?php

namespace Tests\Feature;

use App\Enums\Page\Status;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Features;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_home_page(): void
    {
        // @phpstan-ignore-next-line
        $this->get('/')
            ->assertOk()
            ->assertSeeLivewire(\App\Livewire\Home::class);
    }

    public function test_unverified_user_cannot_access_home_page(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/email/verify');
    }

    public function test_verified_user_can_access_home_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => Carbon::now(),
        ]);

        // @phpstan-ignore-next-line
        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSeeLivewire(\App\Livewire\Home::class);
    }

    public function test_guest_can_access_a_page_php_route(): void
    {
        $page = Page::factory()->create(['status' => Status::Publish]);
        $this->get(route('pages.show', $page))
            ->assertOk();
    }

    public function test_unverified_user_cannot_access_a_page_php_route(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $page = Page::factory()->create(['status' => Status::Publish]);

        $this->actingAs($user)
            ->get(route('pages.show', $page))
            ->assertRedirect('/email/verify');
    }

    public function test_verified_user_can_access_a_page_php_route(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => Carbon::now(),
        ]);

        $page = Page::factory()->create(['status' => Status::Publish]);

        $this->actingAs($user)
            ->get(route('pages.show', $page))
            ->assertOk();
    }

    public function test_guest_can_access_age_rating_guidelines_page(): void
    {
        $page = Page::factory()->create(['status' => Status::Publish]);
        config()->set('page.age_ratings', $page->id);

        $this->get('/age-rating-guidelines')
            ->assertOk();
    }

    public function test_guest_cannot_access_unpublished_age_rating_guidelines_page(): void
    {
        $page = Page::factory()->create(['status' => Status::Draft]);
        config()->set('page.age_ratings', $page->id);

        $this->get('/age-rating-guidelines')
            ->assertNotFound();

        // Test for non-existent page ID
        config()->set('page.age_ratings', 99999); // A non-existent ID
        $this->get('/age-rating-guidelines')
            ->assertNotFound();
    }

    public function test_guest_can_access_cookie_policy_page(): void
    {
        $page = Page::factory()->create(['status' => Status::Publish]);
        config()->set('page.cookie', $page->id);

        $this->get('/cookie-policy')
            ->assertOk();
    }

    public function test_guest_cannot_access_unpublished_cookie_policy_page(): void
    {
        $page = Page::factory()->create(['status' => Status::Draft]);
        config()->set('page.cookie', $page->id);

        $this->get('/cookie-policy')
            ->assertNotFound();

        // Test for non-existent page ID
        config()->set('page.cookie', 99999); // A non-existent ID
        $this->get('/cookie-policy')
            ->assertNotFound();
    }
}
