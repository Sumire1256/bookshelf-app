<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * 書籍をお気に入りに追加/解除する
     *
     * @param  Book  $book  お気に入りの対象となる書籍
     * @return RedirectResponse 前のページにリダイレクトするレスポンス
     */
    public function toggle(Book $book): RedirectResponse
    {
        $user = auth()->user();

        if ($user->favoriteBooks->contains($book->id)) {
            $user->favoriteBooks()->detach($book->id);
        } else {
            $user->favoriteBooks()->attach($book->id);
        }

        return back();
    }

    /**
     * お気に入りの書籍一覧を表示する
     *
     * @return View お気に入りの書籍一覧のビュー
     */
    public function index(): View
    {
        $user = auth()->user();
        $books = $user->favoriteBooks()->latest()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
