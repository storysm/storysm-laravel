<?php

namespace Database\Seeders;

use App\Models\StoryComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoryCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        StoryComment::factory(50)->make()->each(function ($comment) use ($users) {
            $comment->creator_id = $users->random()->id;
            $comment->save();
        });
    }
}
