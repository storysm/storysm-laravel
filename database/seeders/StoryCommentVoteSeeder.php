<?php

namespace Database\Seeders;

use App\Models\StoryComment;
use App\Models\StoryCommentVote;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoryCommentVoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $comments = StoryComment::all();

        $votesToCreate = 200; // Number of votes to attempt to create
        $createdVotes = 0;

        // To avoid unique constraint violations, we'll keep track of voted pairs
        $votedPairs = [];

        while ($createdVotes < $votesToCreate && count($votedPairs) < ($users->count() * $comments->count())) {
            $user = $users->random();
            $comment = $comments->random();

            $pairKey = $user->id.'-'.$comment->id;

            if (! isset($votedPairs[$pairKey])) {
                try {
                    StoryCommentVote::create([
                        'creator_id' => $user->id,
                        'story_comment_id' => $comment->id,
                        'type' => rand(0, 1) ? 'upvote' : 'downvote',
                    ]);
                    $votedPairs[$pairKey] = true;
                    $createdVotes++;
                } catch (\Illuminate\Database\QueryException $e) {
                    // This catch block is primarily for debugging or unexpected unique constraint issues
                    // In a seeder, it's often better to prevent the issue than catch it.
                    // However, given the random selection, a collision is possible if not tracked.
                    if ($e->getCode() === '23000') { // MySQL error code for integrity constraint violation
                        // Duplicate entry, skip and try again
                        $votedPairs[$pairKey] = true; // Mark as attempted to avoid infinite loop on this pair
                    } else {
                        throw $e; // Re-throw other exceptions
                    }
                }
            }
        }
    }
}
