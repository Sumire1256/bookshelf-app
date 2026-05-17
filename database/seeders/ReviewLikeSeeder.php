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

        $users->each(function ($user) use ($reviews) {
            $likeableReviews = $reviews->reject(
                fn ($review) => $review->user_id === $user->id
            );

            $count = rand(0, 3);
            if ($count > 0) {
                $user->likedReviews()->syncWithoutDetaching(
                    $likeableReviews->random($count)->pluck('id')
                );
            }
        });
    }
}
