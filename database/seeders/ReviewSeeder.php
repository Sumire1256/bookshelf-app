<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();
        $comments = [
            1 => 'あまり良くなかったです。',
            2 => '少し物足りなかったです。',
            3 => '普通でした。',
            4 => '良かったです。',
            5 => 'とても良かったです!',
        ];

        $books->each(function ($book) use ($users, $comments) {
            $reviewCount = rand(2, 4);
            $selectedUsers = $users->random($reviewCount);

            $selectedUsers->each(function ($user) use ($book, $comments) {
                $rating = rand(1, 5);

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            });
        });
    }
}
