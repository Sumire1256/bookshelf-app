<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:50'],
            'isbn' => ['nullable', 'string', 'digits:13', Rule::unique('books', 'isbn')->whereNotNull('isbn')],
            'published_date' => ['nullable', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genres' => ['required', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者名を入力してください',
            'author.max' => '著者名は50文字以内で入力してください',
            'isbn.digits' => 'ISBNコードは13桁で入力してください',
            'isbn.unique' => 'この書籍は既に登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '出版日は有効な日付で入力してください',
            'published_date.before_or_equal' => '出版日は今日以前の日付を入力してください',
            'description.max' => '説明は10000文字以内で入力してください',
            'image_url.url' => '画像URLは有効なURLで入力してください',
            'image_url.max' => '画像URLは255文字以内で入力してください',
            'genres.required' => 'ジャンルは１つ以上選択してください',
            'genres.array' => 'ジャンルの形式が正しくありません',
            'genres.*.exists' => '選択されたジャンルは存在しません',
        ];
    }
}
