<?php

namespace App\Http\Controllers;

use App\Models\FavoriteTag;
use App\Models\Note;
use App\Models\Tag;
use App\Models\NoteHistory;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query();

        // タイトル検索
        if ($request->filled('title')) {
            $query->where('title', 'like', '%'.$request->title.'%');
        }

        // タグ検索
        if ($request->filled('tag')) {
            $tagGroups = array_map('trim', explode(',', html_entity_decode($request->tag)));

            $query->where(function ($mainQuery) use ($tagGroups) {
                foreach ($tagGroups as $tagGroup) {
                    if (str_contains($tagGroup, '::')) {
                        $andTags = explode('::', $tagGroup);
                        $mainQuery->orWhere(function ($subQuery) use ($andTags) {
                            foreach ($andTags as $andTag) {
                                $subQuery->whereHas('tags', function ($q) use ($andTag) {
                                    $q->where('name', $andTag);
                                });
                            }
                        });
                    } else {
                        // OR条件の単体タグ
                        $mainQuery->orWhereHas('tags', function ($q) use ($tagGroup) {
                            $q->where('name', $tagGroup);
                        });
                    }
                }
            });
        }

        $notes = $query->orderBy('updated_at', 'desc')->paginate(30)->appends($request->all());
        $favoriteTags = FavoriteTag::with('tag')->ordered()->get();

        return view('home', compact('notes', 'favoriteTags'));
    }

    public function show($id)
    {
        try {
            $note = Note::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/')->with('error', '指定されたノートが見つかりません。');
        }

        // 手動で改行を2つの改行（段落区切り）に変換してからMarkdownを処理
        $bodyWithBreaks = str_replace("\n", "\n\n", $note->body);
        
        $converter = new CommonMarkConverter();
        $note->body = $converter->convertToHtml($bodyWithBreaks);

        return view('notes', compact('note'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:1',
            'body' => 'required|string|min:1|max:65535',
            'tags' => 'nullable|string|max:1000',
        ], [
            'title.required' => 'タイトルは必須です。',
            'title.min' => 'タイトルは少なくとも1文字以上入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'body.required' => '内容は必須です。',
            'body.min' => '内容は少なくとも1文字以上入力してください。',
            'body.max' => '内容は65535文字以内で入力してください。',
            'tags.max' => 'タグは1000文字以内で入力してください。',
        ]);

        $note = Note::create([
            'title' => $request->title,
            'body' => $request->body,
        ]);

        // タグの処理
        if ($request->filled('tags')) {
            $tagNames = array_map('trim', explode(',', $request->tags));
            $tags = [];
            $validTagNames = [];

            foreach ($tagNames as $tagName) {
                if (! empty($tagName)) {
                    // タグ名のバリデーション
                    if (strlen($tagName) > 50) {
                        return back()->withErrors(['tags' => '各タグは50文字以内で入力してください。'])->withInput();
                    }

                    if (! preg_match('/^[\p{L}\p{N}\p{M}_\-\s]+$/u', $tagName)) {
                        return back()->withErrors(['tags' => 'タグには文字、数字、アンダースコア、ハイフンのみ使用できます。'])->withInput();
                    }

                    $validTagNames[] = $tagName;
                }
            }

            // 重複チェック
            $uniqueTagNames = array_unique($validTagNames);
            if (count($uniqueTagNames) > 20) {
                return back()->withErrors(['tags' => 'タグは20個以内で入力してください。'])->withInput();
            }

            foreach ($uniqueTagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tags[] = $tag->id;
            }

            $note->tags()->sync($tags);
        }

        return redirect()->route('notes.show', $note->id)->with('success', 'ノートが作成されました。');
    }

    public function edit($id)
    {
        try {
            $note = Note::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/')->with('error', '指定されたノートが見つかりません。');
        }
        $tagNames = $note->tags->pluck('name')->implode(', ');

        return view('notes.edit', compact('note', 'tagNames'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:1',
            'body' => 'required|string|min:1|max:65535',
            'tags' => 'nullable|string|max:1000',
        ], [
            'title.required' => 'タイトルは必須です。',
            'title.min' => 'タイトルは少なくとも1文字以上入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'body.required' => '内容は必須です。',
            'body.min' => '内容は少なくとも1文字以上入力してください。',
            'body.max' => '内容は65535文字以内で入力してください。',
            'tags.max' => 'タグは1000文字以内で入力してください。',
        ]);

        try {
            $note = Note::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/')->with('error', '指定されたノートが見つかりません。');
        }

        $note->update([
            'title' => $request->title,
            'body' => $request->body,
        ]);

        // タグの処理
        if ($request->filled('tags')) {
            $tagNames = array_map('trim', explode(',', $request->tags));
            $tags = [];
            $validTagNames = [];

            foreach ($tagNames as $tagName) {
                if (! empty($tagName)) {
                    // タグ名のバリデーション
                    if (strlen($tagName) > 50) {
                        return back()->withErrors(['tags' => '各タグは50文字以内で入力してください。'])->withInput();
                    }

                    if (! preg_match('/^[\p{L}\p{N}\p{M}_\-\s]+$/u', $tagName)) {
                        return back()->withErrors(['tags' => 'タグには文字、数字、アンダースコア、ハイフンのみ使用できます。'])->withInput();
                    }

                    $validTagNames[] = $tagName;
                }
            }

            // 重複チェック
            $uniqueTagNames = array_unique($validTagNames);
            if (count($uniqueTagNames) > 20) {
                return back()->withErrors(['tags' => 'タグは20個以内で入力してください。'])->withInput();
            }

            foreach ($uniqueTagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tags[] = $tag->id;
            }

            $note->tags()->sync($tags);
        } else {
            $note->tags()->detach();
        }

        return redirect()->route('notes.show', $note->id)->with('success', 'ノートが更新されました。');
    }

    public function destroy($id)
    {
        try {
            $note = Note::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/')->with('error', '指定されたノートが見つかりません。');
        }
        $note->tags()->detach();
        $note->delete();

        return redirect('/')->with('success', 'ノートが削除されました。');
    }

    public function history($id)
    {
        try {
            $note = Note::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/')->with('error', '指定されたノートが見つかりません。');
        }

        $histories = $note->histories()->with('note')->paginate(30);

        return view('notes.history', compact('note', 'histories'));
    }

    public function restore($id, $version)
    {
        try {
            $note = Note::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/')->with('error', '指定されたノートが見つかりません。');
        }

        if ($note->restoreVersion($version)) {
            return redirect()->route('notes.show', $note->id)->with('success', 'バージョン ' . $version . ' に復元しました。');
        } else {
            return redirect()->route('notes.history', $note->id)->with('error', '指定されたバージョンが見つかりません。');
        }
    }

    public function convertMarkdown(Request $request)
    {
        $request->validate([
            'markdown' => 'required|string|max:1000'
        ]);

        $converter = new CommonMarkConverter();
        $html = $converter->convert($request->markdown)->getContent();

        return response()->json(['html' => $html]);
    }
}
