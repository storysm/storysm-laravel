<?php

namespace Tests\Unit\Policies;

use App\Models\AgeRating;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeRatingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure permissions exist for testing
        Permission::findOrCreate('view_any_age::rating');
        Permission::findOrCreate('view_age::rating');
        Permission::findOrCreate('create_age::rating');
        Permission::findOrCreate('update_age::rating');
        Permission::findOrCreate('delete_age::rating');
    }

    public function test_authorized_user_can_view_any_age_rating(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_age::rating');

        $this->assertTrue($user->can('viewAny', AgeRating::class));
    }

    public function test_unauthorized_user_cannot_view_any_age_rating(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', AgeRating::class));
    }

    public function test_authorized_user_can_view_a_specific_age_rating(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_age::rating');
        $ageRating = AgeRating::factory()->create();

        $this->assertTrue($user->can('view', $ageRating));
    }

    public function test_unauthorized_user_cannot_view_a_specific_age_rating(): void
    {
        $user = User::factory()->create();
        $ageRating = AgeRating::factory()->create();

        $this->assertFalse($user->can('view', $ageRating));
    }

    public function test_authorized_user_can_create_age_ratings(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('create_age::rating');

        $this->assertTrue($user->can('create', AgeRating::class));
    }

    public function test_unauthorized_user_cannot_create_age_ratings(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('create', AgeRating::class));
    }

    public function test_authorized_user_can_update_an_age_rating(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('update_age::rating');
        $ageRating = AgeRating::factory()->create();

        $this->assertTrue($user->can('update', $ageRating));
    }

    public function test_unauthorized_user_cannot_update_an_age_rating(): void
    {
        $user = User::factory()->create();
        $ageRating = AgeRating::factory()->create();

        $this->assertFalse($user->can('update', $ageRating));
    }

    public function test_authorized_user_can_delete_an_unreferenced_age_rating(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('delete_age::rating');
        $ageRating = AgeRating::factory()->create();

        $this->assertFalse($ageRating->isReferenced());
        $this->assertTrue($user->can('delete', $ageRating));
    }

    public function test_authorized_user_cannot_delete_a_referenced_age_rating(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('delete_age::rating');
        $ageRating = AgeRating::factory()->create();
        $story = Story::factory()->create();
        $ageRating->stories()->attach($story);

        $this->assertTrue($ageRating->isReferenced());
        $this->assertFalse($user->can('delete', $ageRating));
    }

    public function test_unauthorized_user_cannot_delete_any_age_rating(): void
    {
        $user = User::factory()->create();
        $ageRating = AgeRating::factory()->create();

        $this->assertFalse($user->can('delete', $ageRating));
    }
}
