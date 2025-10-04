@extends('layouts.app')

@section('title', 'ノート履歴')

@section('content')
    <h1 class="text-2xl font-bold mb-4">「{{ $note->title }}」の履歴</h1>
    
    <div class="mb-4">
        <a href="{{ route('notes.show', $note->id) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors duration-200">ノートに戻る</a>
    </div>

    @if($histories->count() > 0)
        <div class="space-y-4">
            @foreach($histories as $history)
                <div class="bg-white p-4 rounded shadow border {{ $history->change_type === 'created' ? 'border-green-200' : ($history->change_type === 'deleted' ? 'border-red-200' : 'border-blue-200') }}">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold">バージョン {{ $history->version }}</span>
                            <span class="inline-block px-2 py-1 text-xs rounded {{ $history->change_type === 'created' ? 'bg-green-100 text-green-700' : ($history->change_type === 'deleted' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $history->change_type === 'created' ? '作成' : ($history->change_type === 'deleted' ? '削除' : '更新') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-xs">{{ \Carbon\Carbon::parse($history->created_at)->format('Y/m/d H:i') }}</span>
                            @if($history->change_type !== 'deleted')
                                <form action="{{ route('notes.restore', [$note->id, $history->version]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 text-xs rounded hover:bg-green-600 transition-colors duration-200" onclick="return confirm('バージョン {{ $history->version }} に復元しますか？')">復元</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-700 mb-2">
                        <strong>タイトル:</strong> {{ $history->title }}
                    </div>
                    
                    @if($history->tags_snapshot && count($history->tags_snapshot) > 0)
                        <div class="mb-2">
                            <span class="text-sm text-gray-600">タグ: </span>
                            @foreach($history->tags_snapshot as $tag)
                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="text-sm">
                        <strong class="text-gray-600">内容:</strong>
                        <div class="mt-1 p-2 bg-gray-50 rounded text-xs max-h-32 overflow-y-auto">
                            {{ Str::limit($history->body, 200) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            @if($histories->hasPages())
                <div class="text-center mb-4">
                    <span class="text-sm text-gray-600">
                        ページ {{ $histories->currentPage() }} / {{ $histories->lastPage() }} 
                        （全 {{ $histories->total() }} 件中 {{ $histories->firstItem() }}-{{ $histories->lastItem() }} 件を表示）
                    </span>
                </div>
            @endif
            
            <div class="flex justify-center">
                {{ $histories->links() }}
            </div>
        </div>
    @else
        <div class="bg-gray-100 p-4 rounded text-center text-gray-600">
            履歴がありません。
        </div>
    @endif
@endsection