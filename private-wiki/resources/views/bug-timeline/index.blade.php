@extends('layouts.app')

@section('title', 'バグタイムライン')

@section('content')
<div class="max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">🐛 バグタイムライン</h1>
    
    {{-- バグ投稿フォーム --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">新しいバグを報告</h2>
        
        <form method="POST" action="{{ route('bug-timeline.store') }}" class="space-y-4">
            @csrf
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    タイトル <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="バグの概要を入力してください"
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    詳細 <span class="text-red-500">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="バグの詳細、再現手順、期待する動作などを記載してください"
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium">
                    投稿する
                </button>
            </div>
        </form>
    </div>
    
    {{-- バグタイムライン --}}
    <div class="space-y-6">
        @forelse($bugReports as $bugReport)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $bugReport->title }}</h3>
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-2 {{ $bugReport->status === 'open' ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                {{ $bugReport->status === 'open' ? 'オープン' : 'クローズ' }}
                            </span>
                            <span>{{ $bugReport->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        {{-- ステータス更新フォーム --}}
                        <form method="POST" action="{{ route('bug-timeline.update-status', $bugReport) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $bugReport->status === 'open' ? 'closed' : 'open' }}">
                            <button type="submit" 
                                    class="text-sm px-3 py-1 rounded border {{ $bugReport->status === 'open' ? 'border-green-600 text-green-600 hover:bg-green-50' : 'border-red-600 text-red-600 hover:bg-red-50' }}">
                                {{ $bugReport->status === 'open' ? '解決済みにする' : '再オープンする' }}
                            </button>
                        </form>
                        
                        {{-- 削除ボタンとモーダル --}}
                        <div x-data="{ showDeleteConfirm: false }">
                            <button @click="showDeleteConfirm = true" 
                                    class="text-sm px-3 py-1 rounded border border-red-600 text-red-600 hover:bg-red-50">
                                削除
                            </button>
                            
                            {{-- 削除確認モーダル --}}
                            <div x-show="showDeleteConfirm" 
                                 x-transition.opacity.duration.300ms 
                                 class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" 
                                 style="display: none;">
                                <div @click.away="showDeleteConfirm = false" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform scale-90" 
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 transform scale-100" 
                                     x-transition:leave-end="opacity-0 transform scale-90"
                                     class="bg-white rounded-lg p-6 max-w-sm mx-4 shadow-xl">
                                    
                                    <div class="flex items-center mb-4">
                                        <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                            </path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">バグレポートの削除</h3>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-6">
                                        「{{ $bugReport->title }}」を削除してもよろしいですか？<br>
                                        この操作は取り消せません。
                                    </p>
                                    
                                    <div class="flex justify-end space-x-3">
                                        <button @click="showDeleteConfirm = false" 
                                                class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors duration-200">
                                            キャンセル
                                        </button>
                                        
                                        <form method="POST" action="{{ route('bug-timeline.destroy', $bugReport) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors duration-200">
                                                削除する
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-gray-700">
                    <p class="whitespace-pre-wrap">{{ $bugReport->description }}</p>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">バグレポートがありません</p>
                <p class="text-gray-400 text-sm mt-2">上記のフォームから新しいバグを報告してください</p>
            </div>
        @endforelse
    </div>
</div>
@endsection