<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /**
     * レビューを保存する
     */
    public function store(ReviewRequest $request, Book $book): RedirectResponse
    {
        Review::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            ...$request->validated(),
        ]);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました');
    }
}
