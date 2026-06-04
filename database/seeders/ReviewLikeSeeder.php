<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        $reviews->each(function ($review) use ($users) {
            $likeableUsers = $users->where('id', '!=', $review->user_id);

            $count = rand(0, 3);
            if ($count > 0 && $likeableUsers->count() >= $count) {
                $review->likedByUsers()->syncWithoutDetaching(
                    $likeableUsers->random($count)->pluck('id')
                );
            }
        });
    }
}
