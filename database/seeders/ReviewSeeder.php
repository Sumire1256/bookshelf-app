<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $reviews = [
            // 吾輩は猫である（book_id: 1）
            ['book_id' => 1, 'user_id' => 1, 'rating' => 5, 'comment' => '猫の視点から人間社会を鋭く風刺した名作。'],
            ['book_id' => 1, 'user_id' => 2, 'rating' => 4, 'comment' => '漱石の文体が心地よく、すらすら読めた。'],
            ['book_id' => 1, 'user_id' => 3, 'rating' => 4, 'comment' => '明治時代の社会風刺が面白い。'],

            // 人を動かす（book_id: 2）
            ['book_id' => 2, 'user_id' => 4, 'rating' => 5, 'comment' => '人間関係に悩んでいる人に必読の一冊。'],
            ['book_id' => 2, 'user_id' => 5, 'rating' => 5, 'comment' => '何十年も読み継がれている理由がよくわかった。'],
            ['book_id' => 2, 'user_id' => 1, 'rating' => 4, 'comment' => 'ビジネスだけでなくプライベートでも役立つ。'],

            // リーダブルコード（book_id: 3）
            ['book_id' => 3, 'user_id' => 2, 'rating' => 5, 'comment' => 'エンジニアなら必ず読むべき一冊。'],
            ['book_id' => 3, 'user_id' => 3, 'rating' => 3, 'comment' => '日々の開発で意識すべきことが具体的に書かれている。'],
            ['book_id' => 3, 'user_id' => 4, 'rating' => 4, 'comment' => 'チーム開発において特に参考になる内容だった。'],

            // ７つの習慣（book_id: 4）
            ['book_id' => 4, 'user_id' => 5, 'rating' => 4, 'comment' => '人生の指針となる本。習慣の大切さを深く考えさせられた。'],
            ['book_id' => 4, 'user_id' => 1, 'rating' => 4, 'comment' => '自己啓発書の中でも特に内容が濃い。'],
            ['book_id' => 4, 'user_id' => 2, 'rating' => 4, 'comment' => 'ビジネスパーソンとして成長するためのヒントが満載。'],

            // 坊っちゃん（book_id: 5）
            ['book_id' => 5, 'user_id' => 3, 'rating' => 5, 'comment' => '痛快な主人公の行動が気持ちいい。'],
            ['book_id' => 5, 'user_id' => 4, 'rating' => 4, 'comment' => '正義感あふれる主人公に共感できる。'],

            // サピエンス全史（book_id: 6）
            ['book_id' => 6, 'user_id' => 5, 'rating' => 5, 'comment' => '読後に世界の見方が変わった。'],
            ['book_id' => 6, 'user_id' => 1, 'rating' => 5, 'comment' => '歴史と科学が融合した圧巻の一冊。'],
            ['book_id' => 6, 'user_id' => 2, 'rating' => 5, 'comment' => '歴史の見方が根本から変わるような刺激的な内容だった。'],

            // Clean Code（book_id: 7）
            ['book_id' => 7, 'user_id' => 3, 'rating' => 4, 'comment' => 'クリーンなコードとは何かが明確にわかる。'],
            ['book_id' => 7, 'user_id' => 4, 'rating' => 4, 'comment' => 'リーダブルコードと合わせて読むとより効果的。'],

            // 嫌われる勇気（book_id: 8）
            ['book_id' => 8, 'user_id' => 5, 'rating' => 4, 'comment' => 'アドラー心理学を対話形式でわかりやすく解説。'],
            ['book_id' => 8, 'user_id' => 1, 'rating' => 3, 'comment' => '自分らしく生きるヒントをもらえた一冊。'],
            ['book_id' => 8, 'user_id' => 2, 'rating' => 4, 'comment' => '人間関係に悩んでいる人に強くおすすめしたい。'],

            // 火花（book_id: 9）
            ['book_id' => 9, 'user_id' => 3, 'rating' => 4, 'comment' => '又吉さんの文章力の高さに驚いた。'],
            ['book_id' => 9, 'user_id' => 4, 'rating' => 4, 'comment' => '芥川賞受賞も納得の完成度。'],
            ['book_id' => 9, 'user_id' => 5, 'rating' => 3, 'comment' => 'テーマが深く考えさせられる。'],

            // FACTFULNESS（book_id: 10）
            ['book_id' => 10, 'user_id' => 1, 'rating' => 5, 'comment' => '思い込みや偏見を見直すきっかけになった。'],
            ['book_id' => 10, 'user_id' => 2, 'rating' => 5, 'comment' => 'ニュースの見方が大きく変わった。'],
            ['book_id' => 10, 'user_id' => 3, 'rating' => 4, 'comment' => '楽観的に未来を捉えられるようになった。'],

            // コンテナ物語（book_id: 11）
            ['book_id' => 11, 'user_id' => 4, 'rating' => 4, 'comment' => '物流の重要性を再認識させられた。'],
            ['book_id' => 11, 'user_id' => 5, 'rating' => 4, 'comment' => 'ビジネス書としても読み応えがある。'],
            ['book_id' => 11, 'user_id' => 1, 'rating' => 3, 'comment' => 'コンテナの歴史をここまで掘り下げた本は貴重。'],
            ['book_id' => 11, 'user_id' => 2, 'rating' => 4, 'comment' => '物流業界に興味がある人には特におすすめ。'],
        ];

        collect($reviews)->each(function ($reviewData) {
            Review::create($reviewData);
        });
    }
}
