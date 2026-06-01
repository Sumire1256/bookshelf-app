<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * ユーザーがレビューを更新・削除できるか
     *
     * @param  User  $user  認証済みユーザー
     * @param  Review  $review  対象のレビュー
     * @return bool レビューを更新・削除できる場合はtrue、そうでない場合はfalse
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
