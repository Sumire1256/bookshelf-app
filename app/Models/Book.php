<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * 書籍に関連するレビューを取得する。
     *
     * @return HasMany 書籍に関連するレビューのリレーション
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 書籍に関連するジャンルを取得する。
     *
     * @return BelongsToMany 書籍に関連するジャンルのリレーション
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre')->withTimestamps();
    }

    /**
     * 書籍を登録したユーザーを取得する。
     *
     * @return BelongsTo 書籍を登録したユーザーのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
