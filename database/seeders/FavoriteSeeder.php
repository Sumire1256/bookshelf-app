<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $users->each(function ($user) use ($books) {
            $favoriteBooks = $books->random(rand(3, 5))->pluck('id');
            $user->favoriteBooks()->syncWithoutDetaching($favoriteBooks);
        });

    }
}
