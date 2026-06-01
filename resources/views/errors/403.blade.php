<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            エラー
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="text-6xl font-bold text-gray-300 mb-4">403</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">
                        アクセス権限がありません
                    </h3>
                    <p class="text-gray-500 mb-6">
                        このページを表示する権限がありません。
                    </p>
                    <a href="{{ route('books.index') }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        トップページに戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>