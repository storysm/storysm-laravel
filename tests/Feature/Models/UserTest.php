<?php

namespace Tests\Feature\Models;

use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_votes(): void
    {
        $user = User::factory()->create();
        $votes = Vote::factory()->count(3)->for($user, 'creator')->create();

        $this->assertInstanceOf(Collection::class, $user->votes);
        $this->assertCount(3, $user->votes);
        $this->assertTrue($user->votes->contains($votes->firstOrFail()));
    }
}
