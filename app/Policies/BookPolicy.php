<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * ユーザーが   書籍を更新・削除できるか
     *
     * @param  User  $user  認証済みユーザー
     * @param  Book  $book  対象の書籍
     * @return bool 書籍を更新・削除できる場合はtrue、そうでない場合はfalse
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
