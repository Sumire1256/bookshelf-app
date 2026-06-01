<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    /**
     * ISBNから書籍情報を取得する
     *
     * @param  string  $isbn  検索するISBN
     * @return array|null 書籍情報の配列。見つからない場合はnull
     *
     * @throws \Exception APIの利用制限に達した場合や書籍情報の取得に失敗した場合に例外を投げる
     */
    public function fetchByIsbn(string $isbn): ?array
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:'.$isbn,
            'key' => config('services.google_books.api_key'),
        ]);

        if ($response->status() === 429) {
            throw new \Exception('APIの利用制限に達しました。しばらく時間をおいてから再度お試しください', 429);
        }

        if ($response->failed()) {
            throw new \Exception('書籍情報の取得に失敗しました', 503);
        }

        $item = collect($response->json('items'))->first();

        return $item ? $this->formatBookInfo(collect($item['volumeInfo'])) : null;
    }

    /**
     * 書籍情報を整形する
     *
     * @param  Collection  $volumeInfo  GoogleBooksAPIのvolumeInfo
     * @return array 書籍情報の配列
     */
    private function formatBookInfo(Collection $volumeInfo): array
    {
        return [
            'title' => $volumeInfo->get('title'),
            'author' => collect($volumeInfo->get('authors', []))->implode(', ') ?: null,
            'description' => $volumeInfo->get('description'),
            'published_date' => $volumeInfo->get('publishedDate'),
            'image_url' => data_get($volumeInfo->get('imageLinks'), 'thumbnail'),
        ];
    }
}
