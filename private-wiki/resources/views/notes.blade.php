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
        <a href="{{ route('notes.edit', $note->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">編集</a>
        <form action="{{ route('notes.destroy', $note->id) }}" method="POST" class="inline" onsubmit="return confirm('このノートを削除してもよろしいですか？')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">削除</button>
        </form>
        <a href="{{ url('/') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">戻る</a>
    </div>
@endsection