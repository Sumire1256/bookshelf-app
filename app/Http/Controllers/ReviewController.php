<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * レビューを保存する
     *
     * @param  ReviewRequest  $request  バリデーション済みのリクエスト
     * @param  Book  $book  レビュー対象の書籍
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

    /**
     * レビューの編集画面を表示する
     *
     * @param  Review  $review  編集対象のレビュー
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     *
     * @param  ReviewRequest  $request  バリデーション済みのリクエスト
     * @param  Review  $review  更新対象のレビュー
     */
    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました');
    }

    /**
     * レビューを削除する
     *
     * @param  Review  $review  削除対象のレビュー
     * @return RedirectResponse 書籍の詳細ページにリダイレクトするレスポンス
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->delete();

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを削除しました');
    }
}
