@extends('layouts.app')

@section('title', 'notes')

@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ $note->title }}</h1>
    <div class="mb-4 flex flex-wrap gap-2 items-center justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            @if($note->tags && count($note->tags))
                @foreach($note->tags as $tag)
                    <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">{{ $tag->name }}</span>
                @endforeach
            @endif
        </div>
        <span class="text-gray-500 text-xs ml-auto">
            最終更新: {{ \Carbon\Carbon::parse($note->updated_at)->format('Y/m/d H:i') }}
        </span>
    </div>
    <div class="bg-white p-4 rounded shadow prose prose-slate max-w-none">
        {!! $note->body !!}
    </div>
    
    <div class="mt-4 flex gap-2">
        <a href="{{ route('notes.edit', $note->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition-colors duration-200">編集</a>
        
        <div x-data="{ showConfirm: false }">
            <button @click="showConfirm = true" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors duration-200">削除</button>
            
            {{-- 削除確認ダイアログ --}}
            <div x-show="showConfirm" x-transition.opacity.duration.300ms class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
                <div @click.away="showConfirm = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90" class="bg-white rounded-lg p-6 max-w-sm mx-4 shadow-xl">
                    <div class="flex items-center mb-4">
                        <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">ノートの削除</h3>
                    </div>
                    <p class="text-gray-600 mb-6">「{{ $note->title }}」を削除してもよろしいですか？<br>この操作は取り消せません。</p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showConfirm = false" class="px-4 py-2 text-gray-500 hover:text-gray-700 transition-colors duration-200">キャンセル</button>
                        <form action="{{ route('notes.destroy', $note->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors duration-200">削除する</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="{{ url('/') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors duration-200">戻る</a>
    </div>
@endsection