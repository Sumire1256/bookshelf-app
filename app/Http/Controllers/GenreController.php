<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する
     *
     * @return View ジャンル一覧のビュー
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンルの登録画面を表示する
     *
     * @return View ジャンル登録のビュー
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンルを保存する
     *
     * @param  StoreGenreRequest  $request  バリデーション済みのリクエスト
     * @return RedirectResponse ジャンルの詳細ページにリダイレクトするレスポンス
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        $genre = Genre::create($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
    }

    /**
     * ジャンルの詳細を表示する
     *
     * @param  Genre  $genre  表示対象のジャンル
     * @return View ジャンル詳細のビュー
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->latest()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンルの編集画面を表示する
     *
     * @param  Genre  $genre  編集対象のジャンル
     * @return View ジャンル編集のビュー
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新する
     *
     * @param  UpdateGenreRequest  $request  バリデーション済みのリクエスト
     * @param  Genre  $genre  更新対象のジャンル
     * @return RedirectResponse ジャンルの詳細ページにリダイレクトするレスポンス
     */
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました');
    }

    /**
     * ジャンルを削除する
     *
     * @param  Genre  $genre  削除対象のジャンル
     * @return RedirectResponse ジャンル一覧ページにリダイレクトするレスポンス
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')->with('error', 'このジャンルは書籍が登録されているため削除できません');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
    }
}
