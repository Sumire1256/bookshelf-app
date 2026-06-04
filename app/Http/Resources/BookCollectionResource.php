<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookCollectionResource extends JsonResource
{
    /**
     * 書籍一覧での書籍のリソース表現
     *
     * @param  Request  $request  リクエスト
     * @return array 書籍のリソース配列
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'genres' => GenreResource::collection($this->genres),
            'image_url' => $this->image_url,
            'reviews_avg_rating' => $this->reviews_avg_rating ? number_format((float) $this->reviews_avg_rating, 2) : null,
            'reviews_count' => $this->reviews_count,
        ];
    }
}
