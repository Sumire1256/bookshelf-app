<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍の一覧を表示する
     */
    public function index(): View
    {
        $books = Book::with('genres')->withAvg('reviews', 'rating')->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍の登録画面を表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を保存する
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエスト
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = auth()->user()->books()->create($request->validated());
        $book->genres()->attach($request->input('genres'));

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました');
    }

    /**
     * 書籍の詳細を表示する
     *
     * @param  Book  $book  表示対象の書籍
     */
    public function show(Book $book): View
    {
        $book = $book->load('genres', 'reviews.user');

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
