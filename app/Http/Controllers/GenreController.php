<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンルの登録画面を表示する
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンルを保存する
     *
     * @param  StoreGenreRequest  $request  バリデーション済みのリクエスト
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        $genre = Genre::create($request->validated());

        return redirect()->route('genres.show', $genre)->with('success', 'ジャンルを登録しました');
    }

    /**
     * ジャンルの詳細を表示する
     *
     * @param  Genre  $genre  表示対象のジャンル
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->latest()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
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
